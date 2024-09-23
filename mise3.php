<?php
// 함수 파일 불러오기
include 'includes/functions.php';

// 대기질 데이터 가져오기
$airQualityRealData = getAirQualityRealData($airQualityApiKey, $airQualityRealtimeServiceUrl, $sidoName);
$airQualityForecastData = getAirQualityForecastData($airQualityApiKey, $airQualityForecastServiceUrl);

// 대기질 실시간 데이터 처리
$pm10Value = isset($airQualityRealData['response']['body']['items'][1]['pm10Value']) ? $airQualityRealData['response']['body']['items'][1]['pm10Value'] : '정보 없음';
$pm25Value = isset($airQualityRealData['response']['body']['items'][1]['pm25Value']) ? $airQualityRealData['response']['body']['items'][1]['pm25Value'] : '정보 없음';
$o3Value = isset($airQualityRealData['response']['body']['items'][1]['o3Value']) ? $airQualityRealData['response']['body']['items'][1]['o3Value'] : '정보 없음';

$ImageUrl1 = $airQualityForecastData['response']['body']['items'][1]['imageUrl1'];
$ImageUrl2 = $airQualityForecastData['response']['body']['items'][1]['imageUrl2'];
$ImageUrl3 = $airQualityForecastData['response']['body']['items'][1]['imageUrl3'];
$ImageUrl4 = $airQualityForecastData['response']['body']['items'][1]['imageUrl4'];
$ImageUrl5 = $airQualityForecastData['response']['body']['items'][1]['imageUrl5'];
$ImageUrl6 = $airQualityForecastData['response']['body']['items'][1]['imageUrl6'];


// 지명으로 좌표 가져오기
$coords = getShortCoordinates($sidoName);
$regionCode = getMidRegionCode($sidoName);

// 날씨 데이터 가져오기
$weatherShortData = getWeatherShortData($weatherApiKey, $weatherShortServiceUrl, $coords['x'], $coords['y']);
$weatherMidTempData = getWeatherMidData($weatherApiKey, $weatherMidTempServiceUrl, $regionCode);
$weatherMidFcstData = getWeatherMidData($weatherApiKey, $weatherMidFcstServiceUrl, $regionCode);

// 찾고자 하는 카테고리 목록
$categories = ["SKY", "T1H", "REH", "PTY"];

// 결과값을 저장할 배열
$results = [
    "SKY" => null,
    "T1H" => null,
    "REH" => null,
    "PTY" => null
];

// items 배열에서 각각의 카테고리의 첫 번째 해당 항목 찾기
foreach ($weatherShortData['response']['body']['items']['item'] as $item) {
    $category = $item['category'];
    if (in_array($category, $categories) && $results[$category] === null) {
        $results[$category] = $item['fcstValue'];
    }

    // 모든 카테고리의 첫 번째 값을 찾으면 루프 종료
    if (!in_array(null, $results)) {
        break;
    }
}

isset($results['T1H']) ? $temp = $results['T1H'] : $temp = '정보 없음';
isset($results['REH']) ? $reh = $results['REH'] : $reh = '정보 없음';
isset($results['SKY']) ? $sky = getSkyStatus($results['SKY']) : $sky = '정보 없음';
isset($results['PTY']) ? $pty = getRainSnowStatus($results['PTY']) : $pty = '정보 없음';

$forecast5day = get5dayForecast($weatherMidTempData, $weatherMidFcstData);
$forecast24h = get24HourForecast($sidoName);

$healthIndex = calculateHealthIndex($pm10Value, $pm25Value);

// 상태에 따른 클래스 지정
$healthClass = '';
if ($healthIndex[0] === '좋음') {
    $healthClass = 'good';
} elseif ($healthIndex[0] === '보통') {
    $healthClass = 'moderate';
} elseif ($healthIndex[0] === '나쁨') {
    $healthClass = 'bad';
} elseif ($healthIndex[0] === '매우 나쁨') {
    $healthClass = 'very-bad';
}

// 뉴스 데이터 (실제로는 API나 RSS 피드에서 가져와야 함)
$news = [
    ['title' => '미세먼지 저감 정책 발표', 'url' => '#'],
    ['title' => '봄철 황사 주의보', 'url' => '#'],
    ['title' => '실내 공기질 관리 방법', 'url' => '#'],
];

$pm10Grade = getPmGrade($pm10Value, 'pm10');
$pm25Grade = getPmGrade($pm25Value, 'pm25');
$o3Grade = getPmGrade($o3Value, 'o3');

// 뉴스 데이터 가져오기
$newsData = getNaverNews("미세먼지");
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>미세로운 생활</title>
    <!-- 부트스트랩 CSS 추가 -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- 부트스트랩 JS 추가 -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- 기존 CSS 파일 불러오기 -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- 헤더 -->
        <header class="main-header text-center py-4">
            <h1 class="bar-title">미세로운 생활</h1>
        </header>

        <!-- 검색 바 -->
        <div class="row my-4">
            <div class="col-md-6 offset-md-3">
                <form action="" method="GET">
                    <select name="sido" class="form-control" onchange="this.form.submit()">
                        <option value="">지역명 선택</option>
                        <option value="서울" <?php if (isset($_GET['sido']) && $_GET['sido'] == "서울") echo "selected"; ?>>서울</option>
                        <option value="부산" <?php if (isset($_GET['sido']) && $_GET['sido'] == "부산") echo "selected"; ?>>부산</option>
                        <option value="대구" <?php if (isset($_GET['sido']) && $_GET['sido'] == "대구") echo "selected"; ?>>대구</option>
                        <option value="인천" <?php if (isset($_GET['sido']) && $_GET['sido'] == "인천") echo "selected"; ?>>인천</option>
                        <option value="광주" <?php if (isset($_GET['sido']) && $_GET['sido'] == "광주") echo "selected"; ?>>광주</option>
                        <option value="대전" <?php if (isset($_GET['sido']) && $_GET['sido'] == "대전") echo "selected"; ?>>대전</option>
                        <option value="울산" <?php if (isset($_GET['sido']) && $_GET['sido'] == "울산") echo "selected"; ?>>울산</option>
                        <option value="경기" <?php if (isset($_GET['sido']) && $_GET['sido'] == "경기") echo "selected"; ?>>경기</option>
                        <option value="강원" <?php if (isset($_GET['sido']) && $_GET['sido'] == "강원") echo "selected"; ?>>강원</option>
                        <option value="충북" <?php if (isset($_GET['sido']) && $_GET['sido'] == "충북") echo "selected"; ?>>충북</option>
                        <option value="충남" <?php if (isset($_GET['sido']) && $_GET['sido'] == "충남") echo "selected"; ?>>충남</option>
                        <option value="전북" <?php if (isset($_GET['sido']) && $_GET['sido'] == "전북") echo "selected"; ?>>전북</option>
                        <option value="전남" <?php if (isset($_GET['sido']) && $_GET['sido'] == "전남") echo "selected"; ?>>전남</option>
                        <option value="경북" <?php if (isset($_GET['sido']) && $_GET['sido'] == "경북") echo "selected"; ?>>경북</option>
                        <option value="경남" <?php if (isset($_GET['sido']) && $_GET['sido'] == "경남") echo "selected"; ?>>경남</option>
                        <option value="제주" <?php if (isset($_GET['sido']) && $_GET['sido'] == "제주") echo "selected"; ?>>제주</option>
                        <option value="세종" <?php if (isset($_GET['sido']) && $_GET['sido'] == "세종") echo "selected"; ?>>세종</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- 날씨 정보 -->
        <div class="row">
            <div class="col-md-12">
                <h2 class="bar-title">현재 날씨</h2>
                <div class="row">
                    <div class="col-md-6">
                        <!-- 지역명 추가 -->
                        <p>지역: <?php echo $sidoName; ?></p>
                        <p>기온: <?php echo $temp; ?>°</p>
                        <p>습도: <?php echo $reh; ?>%</p>
                        <p>하늘 상태: <?php echo $sky; ?></p>
                        <p>강수 형태: <?php echo $pty; ?></p>
                    </div>
                    <div class="col-md-6 text-right">
                        <!-- 시간과 날짜를 아래로 내림 -->
                        <p><?php echo date('H:i'); ?></p>
                        <p><?php echo date('D m-d'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5일 예보 -->
        <div class="row my-4">
            <div class="col-md-12">
                <h2 class="bar-title">5일 예보</h2>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>날짜</th>
                            <th>최저기온</th>
                            <th>최고기온</th>
                            <th>예상 날씨 - 오전</th>
                            <th>예상 날씨 - 오후</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($forecast5day as $item): ?>
                        <tr>
                            <td><?php echo $item['date']; ?></td>
                            <td><?php echo $item['low_temp']; ?></td>
                            <td><?php echo $item['high_temp']; ?></td>
                            <td><?php echo $item['fcstAm']; ?></td>
                            <td><?php echo $item['fcstPm']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 대기질 정보 -->
        <div class="row my-4">
            <div class="col-md-12">
                <h2 class="bar-title">대기질 정보</h2>
                <div class="row">
                    <div class="col-md-4">
                        <p>미세먼지: <?php echo $pm10Value; ?></p>
                        <p style="color: <?php echo $pm10Grade[1]; ?>"><?php echo $pm10Grade[0]; ?></p>
                    </div>
                    <div class="col-md-4">
                        <p>초미세먼지: <?php echo $pm25Value; ?></p>
                        <p style="color: <?php echo $pm25Grade[1]; ?>"><?php echo $pm25Grade[0]; ?></p>
                    </div>
                    <div class="col-md-4">
                        <p>오존: <?php echo $o3Value; ?></p>
                        <p style="color: <?php echo $o3Grade[1]; ?>"><?php echo $o3Grade[0]; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 이미지 섹션 -->
        <div class="row my-4">
            <!-- 첫 번째 이미지 세트 -->
            <div class="col-md-4">
                <img src="<?php echo $ImageUrl1; ?>" class="img-fluid" alt="Image 1">
            </div>
            <div class="col-md-4">
                <img src="<?php echo $ImageUrl2; ?>" class="img-fluid" alt="Image 2">
            </div>
            <div class="col-md-4">
                <img src="<?php echo $ImageUrl3; ?>" class="img-fluid" alt="Image 3">
            </div>
        </div>
        <div class="row my-4">
            <!-- 두 번째 이미지 세트 -->
            <div class="col-md-4">
                <img src="<?php echo $ImageUrl4; ?>" class="img-fluid" alt="Image 4">
            </div>
            <div class="col-md-4">
                <img src="<?php echo $ImageUrl5; ?>" class="img-fluid" alt="Image 5">
            </div>
            <div class="col-md-4">
                <img src="<?php echo $ImageUrl6; ?>" class="img-fluid" alt="Image 6">
            </div>
        </div>

        <!-- 뉴스 섹션 -->
        <div class="row my-4">
            <div class="col-md-12">
                <h2 class="bar-title">미세먼지 관련 뉴스</h2>
                <?php if($newsData && isset($newsData['items'])): ?>
                    <?php foreach ($newsData['items'] as $item): ?>
                    <div class="news-item mb-2">
                        <a href="<?php echo $item['link']; ?>" target="_blank"><?php echo htmlspecialchars(strip_tags($item['title'])); ?></a>
                        <div class="news-date"><?php echo date('Y-m-d H:i', strtotime($item['pubDate'])); ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>뉴스를 불러오는데 실패했습니다.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>