<?php
// 기존 대기질 API 키와 URL
//$airQualityApiKey = "ty1%2FoTRtmUmIDFmploKPKboG%2B9i8MWudFsxHdtcL8hQePmJ9lh9N0kJ%2BeCkrr2bY%2F1gyZ86wDah1%2Fkf27OGFeA%3D%3D";
$airQualityApiKey = "LCuy9eWKCdC6S6qsHWBEl4Yfuxv0UPJP1yJVDl1jxPP5Ne6PbGHPI9%2BQLRWdqOJ1fdGMEhdRONk4O9X7AZPT9A%3D%3D";
//$airQualityServiceUrl = 'http://apis.data.go.kr/B552584/ArpltnInforInqireSvc/getCtprvnRltmMesureDnsty';
$airQualityServiceUrl = "http://apis.data.go.kr/B552584/ArpltnInforInqireSvc/getCtprvnRltmMesureDnsty";

// 날씨 API 키와 URL (예시)
$weatherApiKey = "ty1%2FoTRtmUmIDFmploKPKboG%2B9i8MWudFsxHdtcL8hQePmJ9lh9N0kJ%2BeCkrr2bY%2F1gyZ86wDah1%2Fkf27OGFeA%3D%3D";
$weatherServiceUrl = "https://api.openweathermap.org/data/2.5/forecast";

$sidoName = isset($_GET['sido']) ? $_GET['sido'] : '서울';
$datetime = date('Y-m-d H:i');

// 기존 대기질 데이터 가져오는 함수
function getAirQualityData($apiKey, $serviceUrl, $sidoName) {
    $queryParams = '?' . urlencode('serviceKey') . '=' . $apiKey;
    $queryParams .= '&' . urlencode('returnType') . '=' . urlencode('json');
    $queryParams .= '&' . urlencode('numOfRows') . '=' . urlencode('100');
    $queryParams .= '&' . urlencode('pageNo') . '=' . urlencode('1');
    $queryParams .= '&' . urlencode('sidoName') . '=' . urlencode($sidoName);
    $queryParams .= '&' . urlencode('ver') . '=' . urlencode('1.0');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $serviceUrl . $queryParams);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// 날씨 데이터 가져오는 함수
// function getWeatherData($apiKey, $serviceUrl, $city) {
//     $queryParams = http_build_query([
//         'q' => $city,
//         'appid' => $apiKey,
//         'units' => 'metric',
//         'lang' => 'kr'
//     ]);

//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, $serviceUrl . "?" . $queryParams);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//     curl_setopt($ch, CURLOPT_HEADER, FALSE);
//     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

//     $response = curl_exec($ch);
//     curl_close($ch);

//     return json_decode($response, true);
// }

// 대기질 데이터 가져오기
$airQualityData = getAirQualityData($airQualityApiKey, $airQualityServiceUrl, $sidoName);

// 날씨 데이터 가져오기
// $weatherData = getWeatherData($weatherApiKey, $weatherServiceUrl, $sidoName);

// 데이터 처리
$pm10Value = isset($airQualityData['response']['body']['items'][0]['pm10Value']) ? $airQualityData['response']['body']['items'][0]['pm10Value'] : '정보 없음';
$pm25Value = isset($airQualityData['response']['body']['items'][0]['pm25Value']) ? $airQualityData['response']['body']['items'][0]['pm25Value'] : '정보 없음';
$o3Value = isset($airQualityData['response']['body']['items'][0]['o3Value']) ? $airQualityData['response']['body']['items'][0]['o3Value'] : '정보 없음';

// 현재 날씨 정보
//$currentWeather = [
//    'temp' => $weatherData['list'][0]['main']['temp'],
//    'condition' => $weatherData['list'][0]['weather'][0]['description']
//];

// 5일 예보 정보
// $forecast = [];
// for ($i = 0; $i < 5; $i++) {
//     $forecast[] = [
//         'day' => date('D', strtotime("+$i days")),
//         'temp' => $weatherData['list'][$i * 8]['main']['temp'],
//         'condition' => $weatherData['list'][$i * 8]['weather'][0]['description']
//     ];
// }
$currentWeather = [];

// 24시간 예보 데이터 가져오기 (가상의 함수, 실제 API에 맞게 구현 필요)
function get24HourForecast($city) {
    // 실제 API 호출 및 데이터 처리 로직 구현 필요
    return [
        ['time' => '12:00', 'pm10' => 30, 'pm25' => 15],
        ['time' => '18:00', 'pm10' => 35, 'pm25' => 18],
        ['time' => '00:00', 'pm10' => 25, 'pm25' => 12],
        ['time' => '06:00', 'pm10' => 20, 'pm25' => 10],
    ];
}

$forecast24h = get24HourForecast($sidoName);

// 건강 지수 계산 함수
function calculateHealthIndex($pm10, $pm25) {
    $index = max($pm10 / 50, $pm25 / 25) * 100;
    if ($index <= 50) return ['좋음', '외출하기 좋은 날씨입니다.'];
    if ($index <= 100) return ['보통', '장시간 외출 시 마스크 착용을 권장합니다.'];
    if ($index <= 150) return ['나쁨', '가능한 외출을 자제하고, 외출 시 반드시 마스크를 착용하세요.'];
    return ['매우 나쁨', '외출을 자제하고 실내에서 공기청정기를 사용하세요.'];
}

$healthIndex = calculateHealthIndex($pm10Value, $pm25Value);

// 뉴스 데이터 (실제로는 API나 RSS 피드에서 가져와야 함)
$news = [
    ['title' => '미세먼지 저감 정책 발표', 'url' => '#'],
    ['title' => '봄철 황사 주의보', 'url' => '#'],
    ['title' => '실내 공기질 관리 방법', 'url' => '#'],
];

function getPmGrade($value, $type) {
    if ($value === '정보 없음') return ['정보 없음', '#808080'];
    $value = floatval($value);
    if ($type == 'pm10') {
        if ($value <= 30) return ['좋음', '#4CAF50'];
        if ($value <= 80) return ['보통', '#FFC107'];
        if ($value <= 150) return ['나쁨', '#FF5722'];
        return ['매우나쁨', '#9C27B0'];
    } elseif ($type == 'pm25') {
        if ($value <= 15) return ['좋음', '#4CAF50'];
        if ($value <= 35) return ['보통', '#FFC107'];
        if ($value <= 75) return ['나쁨', '#FF5722'];
        return ['매우나쁨', '#9C27B0'];
    } elseif ($type == 'o3') {
        if ($value <= 0.030) return ['좋음', '#4CAF50'];
        if ($value <= 0.090) return ['보통', '#FFC107'];
        if ($value <= 0.150) return ['나쁨', '#FF5722'];
        return ['매우나쁨', '#9C27B0'];
    }
}

$pm10Grade = getPmGrade($pm10Value, 'pm10');
$pm25Grade = getPmGrade($pm25Value, 'pm25');
$o3Grade = getPmGrade($o3Value, 'o3');

// 네이버 뉴스 API 함수
function getNaverNews($query, $display = 5) {
    $client_id = "YOUR_CLIENT_ID"; // 네이버 개발자 센터에서 발급받은 클라이언트 ID
    $client_secret = "YOUR_CLIENT_SECRET"; // 네이버 개발자 센터에서 발급받은 클라이언트 시크릿
    $encQuery = urlencode($query);
    $url = "https://openapi.naver.com/v1/search/news.json?query=" . $encQuery . "&display=" . $display . "&sort=date";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $headers = array(
        "X-Naver-Client-Id: " . $client_id,
        "X-Naver-Client-Secret: " . $client_secret
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if($status_code == 200) {
        return json_decode($response, true);
    } else {
        return null;
    }
}

// 뉴스 데이터 가져오기
$newsData = getNaverNews("미세먼지");
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>미세로운 생활</title>
    <style>
        :root {
            --primary-color: #3F6DDC;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --background-color: #f0f0f0;
            --text-color: #333333;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            font-size: 16px;
            line-height: 1.6;
        }

        .weather-widget {
            background-color: #ffffff;
            border-radius: 20px;
            padding: 5%;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .section {
            background-color: var(--background-color);
            border-radius: 15px;
            padding: 5%;
            margin-bottom: 5%;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            font-size: 1.4em;
            font-weight: bold;
            margin-bottom: 0.5em;
            color: var(--secondary-color);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.3em;
        }

        .weather-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .temperature {
            font-size: 3em;
            font-weight: bold;
            color: var(--accent-color);
        }

        .weather-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1em;
            font-size: 1.1em;
        }

        .forecast {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .forecast-day {
            text-align: center;
            font-size: 0.9em;
            flex: 1 1 20%;
            margin-bottom: 1em;
        }

        .search-bar {
            margin-bottom: 1.5em;
        }

        .search-bar input {
            padding: 0.75em;
            width: 70%;
            border: 2px solid var(--primary-color);
            border-radius: 8px;
            font-size: 1em;
        }

        .search-bar button {
            padding: 0.75em 1.25em;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s;
        }

        .search-bar button:hover {
            background-color: #2980b9;
        }

        .air-quality {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .air-quality-item {
            text-align: center;
            flex: 1 1 30%;
            padding: 0.6em;
            margin-bottom: 1em;
        }

        .air-quality-item div:first-child {
            font-weight: bold;
            margin-bottom: 0.3em;
        }

        .air-quality-item div:nth-child(2) {
            font-size: 1.5em;
            font-weight: bold;
        }

        .forecast-24h {
            overflow-x: auto;
        }

        .forecast-24h table {
            width: 100%;
            border-collapse: collapse;
        }

        .forecast-24h th, .forecast-24h td {
            padding: 0.75em;
            text-align: center;
            border: 1px solid #ddd;
        }

        .forecast-24h th {
            background-color: var(--primary-color);
            color: white;
        }

        .health-index h3 {
            font-size: 1.5em;
            color: var(--accent-color);
            margin-bottom: 0.6em;
        }

        .news-item {
            margin-bottom: 1em;
        }

        .news-item a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 1.1em;
        }

        .news-item a:hover {
            text-decoration: underline;
        }

        .news-date {
            font-size: 0.9em;
            color: #666;
        }

        @media (max-width: 768px) {
            body {
                font-size: 14px;
            }

            .weather-widget {
                padding: 1em;
            }

            .section {
                padding: 1em;
            }

            .temperature {
                font-size: 2.5em;
            }

            .forecast-day {
                flex: 1 1 50%;
            }

            .air-quality-item {
                flex: 1 1 100%;
            }

            .search-bar input {
                width: 60%;
            }
        }

        @media (max-width: 480px) {
            body {
                font-size: 12px;
            }

            .temperature {
                font-size: 2em;
            }

            .forecast-day {
                flex: 1 1 100%;
            }

            .search-bar input {
                width: 100%;
                margin-bottom: 0.5em;
            }

            .search-bar button {
                width: 100%;
            }
        }

        .main-header {
            background-color: var(--primary-color);
            color: white;
            text-align: center;
            padding: 1em;
            font-size: 1.5em;
            font-weight: bold;
            margin-bottom: 1em;
        }
    </style>
</head>
<body>
    <div class="weather-widget">
        <header class="main-header">
            미세로운 생활
        </header>

        <div class="section search-bar">
            <form action="" method="GET">
                <input type="text" name="sido" placeholder="지역명 입력">
                <button type="submit">검색</button>
            </form>
        </div>

        <div class="section">
            <div class="weather-header">
                <div>
                    <!-- <div>
                        <?php echo $currentWeather['condition']; ?>
                    </div> -->
                    <div class="temperature">
                        <?php echo $currentWeather['temp']; ?>°
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
                <h3><?php echo $healthIndex[0]; ?></h3>
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

        <div class="section">
            <div class="section-title">5일 예보</div>
            <div class="forecast">
                <?php foreach ($forecast as $day): ?>
                <div class="forecast-day">
                    <div><?php echo $day['day']; ?></div>
                    <div><?php echo getWeatherEmoji($day['condition']); ?></div>
                    <div><?php echo $day['temp']; ?>°</div>
                </div>
                <?php endforeach; ?>
            </div>
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

<?php
function getWeatherEmoji($condition) {
    switch ($condition) {
        case '맑음':
            return '☀️';
        case '흐림':
            return '☁️';
        case '비':
            return '🌧️';
        default:
            return '❓';
    }
}
?>