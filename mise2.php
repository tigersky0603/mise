<?php
// 함수 파일 불러오기
include 'includes/functions.php';

// 대기질 데이터 가져오기
$airQualityData = getAirQualityData($airQualityApiKey, $airQualityServiceUrl, $sidoName);

// 대기질 데이터 처리
$pm10Value = isset($airQualityData['response']['body']['items'][1]['pm10Value']) ? $airQualityData['response']['body']['items'][1]['pm10Value'] : '정보 없음';
$pm25Value = isset($airQualityData['response']['body']['items'][1]['pm25Value']) ? $airQualityData['response']['body']['items'][1]['pm25Value'] : '정보 없음';
$o3Value = isset($airQualityData['response']['body']['items'][1]['o3Value']) ? $airQualityData['response']['body']['items'][1]['o3Value'] : '정보 없음';


// 지명으로 좌표 가져오기
$coords = getShortCoordinates($sidoName);
$regionCode = getMidRegionCode($sidoName);

// 날씨 데이터 가져오기
$weatherShortData = getWeatherShortData($weatherShortApiKey, $weatherShortServiceUrl, $coords['x'], $coords['y']);
$weatherMidTempData = getWeatherMidData($weatherMidTempApiKey, $weatherMidTempServiceUrl, $regionCode);
$weatherMidFcstData = getWeatherMidData($weatherMidFcstApiKey, $weatherMidFcstServiceUrl, $regionCode);

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
</head>
<body>
    <div class="weather-widget">
        <header class="main-header">
            미세로운 생활
        </header>
        <link rel="stylesheet" href="css/style.css"> <!-- CSS 파일 불러오기 -->

        <div class="section search-bar">
            <form action="" method="GET">
                <select name="sido" onchange="this.form.submit()" style="width: 200px; height: 40px;">
                    <option value="">지역명 선택</option>
                    <option value="서울">서울</option>
                    <option value="부산">부산</option>
                    <option value="대구">대구</option>
                    <option value="인천">인천</option>
                    <option value="광주">광주</option>
                    <option value="대전">대전</option>
                    <option value="울산">울산</option>
                    <option value="경기">경기</option>
                    <option value="강원">강원</option>
                    <option value="충북">충북</option>
                    <option value="충남">충남</option>
                    <option value="전북">전북</option>
                    <option value="전남">전남</option>
                    <option value="경북">경북</option>
                    <option value="경남">경남</option>
                    <option value="제주">제주</option>
                    <option value="세종">세종</option>
                </select>
            </form>
        </div>

        <div class="section">
            <div class="weather-header">
                <div>
                    <div>
                        <?php echo '기온: ' . $temp; ?>°
                    </div> 
                    <div>
                        <?php echo '습도: ' . $reh; ?>%
                    </div>
                    <div>
                        <?php echo '하늘상태: ' . $sky; ?>
                    </div>
                    <div>
                        <?php echo '강수형태: ' . $pty; ?>
                    </div>
                </div>
                <div>
                    <div><?php echo date('H:i'); ?></div>
                    <div><?php echo date('D m-d'); ?></div>
                </div>
            </div>
            <div class="weather-details">
                <div><?php echo $sidoName; ?></div>
            </div>
        </div>

        <div class="section forecast-24h">
            <div class="section-title">5일 예보</div>
            <table>
                <tr>
                    <th>날짜</th>
                    <th>최저기온</th>
                    <th>최고기온</th>
                    <th>예상날씨-오전</th>
                    <th>예상날씨-오후</th>
                </tr>
                <?php foreach ($forecast5day as $item): ?>
                <tr>
                    <td><?php echo $item['date']; ?></td>
                    <td><?php echo $item['low_temp']; ?></td>
                    <td><?php echo $item['high_temp']; ?></td>
                    <td><?php echo $item['fcstAm']; ?></td>
                    <td><?php echo $item['fcstPm']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

       <div class="section">
            <div class="section-title">대기질 정보 및 건강 지수</div>
            <div class="air-quality">
                <div class="air-quality-item">
                    <div>미세먼지</div>
                    <div><?php echo $pm10Value; ?></div>
                    <div style="color: <?php echo $pm10Grade[1]; ?>"><?php echo $pm10Grade[0]; ?></div>
                </div>
                <div class="air-quality-item">
                    <div>초미세먼지</div>
                    <div><?php echo $pm25Value; ?></div>
                    <div style="color: <?php echo $pm25Grade[1]; ?>"><?php echo $pm25Grade[0]; ?></div>
                </div>
                <div class="air-quality-item">
                    <div>오존</div>
                    <div><?php echo $o3Value; ?></div>
                    <div style="color: <?php echo $o3Grade[1]; ?>"><?php echo $o3Grade[0]; ?></div>
                </div>
            </div>
            
            <div class="health-index">
                <h3 class="<?php echo $healthClass; ?>"><?php echo $healthIndex[0]; ?></h3>
                <p><?php echo $healthIndex[1]; ?></p>
            </div>
        </div>

        <div class="section forecast-24h">
            <div class="section-title">24시간 예보</div>
            <table>
                <tr>
                    <th>시간</th>
                    <th>PM10</th>
                    <th>PM2.5</th>
                </tr>
                <?php foreach ($forecast24h as $item): ?>
                <tr>
                    <td><?php echo $item['time']; ?></td>
                    <td><?php echo $item['pm10']; ?></td>
                    <td><?php echo $item['pm25']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="section news-section">
            <div class="section-title">미세먼지 관련 뉴스</div>
            <?php if($newsData && isset($newsData['items'])): ?>
                <?php foreach ($newsData['items'] as $item): ?>
                <div class="news-item">
                    <a href="<?php echo $item['link']; ?>" target="_blank"><?php echo htmlspecialchars(strip_tags($item['title'])); ?></a>
                    <div class="news-date"><?php echo date('Y-m-d H:i', strtotime($item['pubDate'])); ?></div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>뉴스를 불러오는데 실패했습니다.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    // 간단한 알림 시스템
    function checkAirQuality() {
        const pm10 = <?php echo $pm10Value; ?>;
        const pm25 = <?php echo $pm25Value; ?>;
        if (pm10 > 100 || pm25 > 50) {
            alert('미세먼지 농도가 높습니다. 외출 시 주의하세요!');
        }
    }
    window.onload = checkAirQuality;
    </script>
</body>
</html>