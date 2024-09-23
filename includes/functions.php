<?php
// 대기질 API 키와 URL 
// 시도별 실시간 측정정보 조회
$airQualityApiKey = "LCuy9eWKCdC6S6qsHWBEl4Yfuxv0UPJP1yJVDl1jxPP5Ne6PbGHPI9%2BQLRWdqOJ1fdGMEhdRONk4O9X7AZPT9A%3D%3D";
$airQualityRealtimeServiceUrl = "http://apis.data.go.kr/B552584/ArpltnInforInqireSvc/getCtprvnRltmMesureDnsty";
$airQualityForecastServiceUrl = "http://apis.data.go.kr/B552584/ArpltnInforInqireSvc/getMinuDustFrcstDspth";

// 날씨 API 키와 URL 
// 단기예보조회
$weatherApiKey = "LCuy9eWKCdC6S6qsHWBEl4Yfuxv0UPJP1yJVDl1jxPP5Ne6PbGHPI9%2BQLRWdqOJ1fdGMEhdRONk4O9X7AZPT9A%3D%3D";
$weatherShortServiceUrl = "http://apis.data.go.kr/1360000/VilageFcstInfoService_2.0/getUltraSrtFcst";
// 중기예보조회
$weatherMidTempServiceUrl = "http://apis.data.go.kr/1360000/MidFcstInfoService/getMidTa";
$weatherMidFcstServiceUrl = "http://apis.data.go.kr/1360000/MidFcstInfoService/getMidLandFcst";

$sidoName = isset($_GET['sido']) ? $_GET['sido'] : '서울';
$datetime = date('Y-m-d H:i');

// 실시간 대기질 데이터 가져오는 함수
function getAirQualityRealData($apiKey, $serviceUrl, $sidoName) {
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

// 대기질 예보통보 가져오기
function getAirQualityForecastData($apiKey, $serviceUrl) {
    // 현재 시각을 받아 DateTime 객체 생성
    $now = new DateTime();
    // 1시간 빼고 계산.
    $now->modify('-1 hour');
    // YYYY-MM-DD 형식의 날짜 문자열 추출
    $base_date = $now->format('Y-m-d');

    $queryParams = '?' . urlencode('serviceKey') . '=' . $apiKey;
    $queryParams .= '&' . urlencode('returnType') . '=' . urlencode('json');
    $queryParams .= '&' . urlencode('numOfRows') . '=' . urlencode('100');
    $queryParams .= '&' . urlencode('pageNo') . '=' . urlencode('1');
    $queryParams .= '&' . urlencode('searchDate') . '=' . urlencode($base_date);
    $queryParams .= '&' . urlencode('InformCode') . '=' . urlencode('PM10');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $serviceUrl . $queryParams);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function getShortCoordinates($regionName) {
    // 지역명과 해당 좌표를 매핑하는 배열
    $coordinates = [
        '서울' => ['x' => 60, 'y' => 127],
        '부산' => ['x' => 98, 'y' => 76],
        '대구' => ['x' => 89, 'y' => 90],
        '인천' => ['x' => 55, 'y' => 124],
        '광주' => ['x' => 58, 'y' => 74],
        '대전' => ['x' => 67, 'y' => 100],
        '울산' => ['x' => 102, 'y' => 84],
        '경기' => ['x' => 60, 'y' => 120],
        '강원' => ['x' => 73, 'y' => 134],
        '충북' => ['x' => 69, 'y' => 107],
        '충남' => ['x' => 68, 'y' => 100],
        '전북' => ['x' => 63, 'y' => 89],
        '전남' => ['x' => 51, 'y' => 67],
        '경북' => ['x' => 87, 'y' => 106],
        '경남' => ['x' => 91, 'y' => 77],
        '제주' => ['x' => 52, 'y' => 38],
        '세종' => ['x' => 66, 'y' => 103],
    ];

    // 지역명이 배열에 존재하는지 확인
    if (array_key_exists($regionName, $coordinates)) {
        return $coordinates[$regionName];
    } else {
        return $coordinates['서울']; // 지역명이 배열에 없으면 서울 반환
    }
}

function getMidRegionCode($regionName) {
    // 지역명과 해당 좌표를 매핑하는 배열
    $regionCode = [
        '서울' => ['11B10101'],
        '부산' => ['11H20201'],
        '대구' => ['11H10701'],
        '인천' => ['11B20201'],
        '광주' => ['11F20501'],
        '대전' => ['11C20401'],
        '울산' => ['11H20101'],
        '경기' => ['11B20601'],
        '강원' => ['11D10301'],
        '충북' => ['11C10301'],
        '충남' => ['11C20301'],
        '전북' => ['11F10201'],
        '전남' => ['11F20503'],
        '경북' => ['11H10602'],
        '경남' => ['11H20304'],
        '제주' => ['11G00201'],
        '세종' => ['11C20404'],
    ];

    // 지역명이 배열에 존재하는지 확인
    if (array_key_exists($regionName, $regionCode)) {
        return $regionCode[$regionName][0];
    } else {
        return $regionCode['서울'][0]; // 지역명이 배열에 없으면 서울 반환
    }
}

function getWeatherShortData($apiKey, $serviceUrl, $nx, $ny) {
    // 현재 시각을 받아 DateTime 객체 생성
    $now = new DateTime();
    // 1시간 빼고 계산.
    $now->modify('-1 hour');
    // YYYYMMDD 형식의 날짜 문자열 추출
    $base_date = $now->format('Ymd');
    // HH00 형식의 시간 문자열 추출 (분을 00으로 고정)
    $base_time = $now->format('H') . '00';

    $queryParams = '?' . urlencode('serviceKey') . '=' . $apiKey;
    $queryParams .= '&' . urlencode('pageNo') . '=' . urlencode('1');
    $queryParams .= '&' . urlencode('numOfRows') . '=' . urlencode('1000');
    $queryParams .= '&' . urlencode('dataType') . '=' . urlencode('JSON');
    $queryParams .= '&' . urlencode('base_date') . '=' . urlencode($base_date);
    $queryParams .= '&' . urlencode('base_time') . '=' . urlencode($base_time);
    $queryParams .= '&' . urlencode('nx') . '=' . urlencode($nx);
    $queryParams .= '&' . urlencode('ny') . '=' . urlencode($ny);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $serviceUrl . $queryParams);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function getWeatherMidData($apiKey, $serviceUrl, $regionCode) {
    // 현재 시각을 받아 DateTime 객체 생성
    $now = new DateTime();
    // YYYYMMDDHH00 형식의 날짜 문자열 추출
    if ($now->format('H') >= 18)
        $base_time = $now->format('Ymd') . '1800';
    else
        $base_time = $now->format('Ymd') . '0600';

    $queryParams = '?' . urlencode('serviceKey') . '=' . $apiKey;
    $queryParams .= '&' . urlencode('pageNo') . '=' . urlencode('1');
    $queryParams .= '&' . urlencode('numOfRows') . '=' . urlencode('1000');
    $queryParams .= '&' . urlencode('dataType') . '=' . urlencode('JSON');
    $queryParams .= '&' . urlencode('regId') . '=' . urlencode($regionCode);
    $queryParams .= '&' . urlencode('tmFc') . '=' . urlencode($base_time);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $serviceUrl . $queryParams);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function getSkyStatus($skyCode) {
    if ($skyCode == 1)
        return "맑음";
    else if ($skyCode == 3)
        return "구름 많음";
    else if ($skyCode == 4)
        return "흐림";
    else
        return "정보 없음";
}

function getRainSnowStatus($ptyCode) {
    if ($ptyCode == 0)
        return "없음";
    else if ($ptyCode == 1)
        return "비";
    else if ($ptyCode == 2)
        return "비/눈";
    else if ($ptyCode == 3)
        return "눈";
    else if ($ptyCode == 5)
        return "빗방울";
    else if ($ptyCode == 6)
        return "빗방울 눈날림";
    else if ($ptyCode == 7)
        return "눈날림";
    else
        return "정보 없음";
}

// 5일 예보 데이터 가져오기
function get5dayForecast($TempData, $FcstData) {
    if ($FcstData['response']['header']['resultMsg'] == 'NO_DATA')
    return [
        ['date' => 'NO Data', 'low_temp' => 'NO Data', 'high_temp' => 'NO Data', 'fcstAm' => 'NO Data', 'fcstPm' => 'NO Data'], 
        ['date' => 'NO Data', 'low_temp' => 'NO Data', 'high_temp' => 'NO Data', 'fcstAm' => 'NO Data', 'fcstPm' => 'NO Data'],
        ['date' => 'NO Data', 'low_temp' => 'NO Data', 'high_temp' => 'NO Data', 'fcstAm' => 'NO Data', 'fcstPm' => 'NO Data'],
        ['date' => 'NO Data', 'low_temp' => 'NO Data', 'high_temp' => 'NO Data', 'fcstAm' => 'NO Data', 'fcstPm' => 'NO Data'],
        ['date' => 'NO Data', 'low_temp' => 'NO Data', 'high_temp' => 'NO Data', 'fcstAm' => 'NO Data', 'fcstPm' => 'NO Data'],
    ];
    else
    $now = new DateTime();
    return [
        ['date' => $now->modify('+3 days')->format('Y-m-d'), 'low_temp' => $TempData['response']['body']['items']['item'][0]['taMin3'], 'high_temp' => $TempData['response']['body']['items']['item'][0]['taMax3'], 
        'fcstAm' => $FcstData['response']['body']['items']['item'][0]['wf3Am'], 'fcstPm' => $FcstData['response']['body']['items']['item'][0]['wf3Pm']], 
        ['date' => $now->modify('+1 days')->format('Y-m-d'), 'low_temp' => $TempData['response']['body']['items']['item'][0]['taMin4'], 'high_temp' => $TempData['response']['body']['items']['item'][0]['taMax4'], 
        'fcstAm' => $FcstData['response']['body']['items']['item'][0]['wf4Am'], 'fcstPm' => $FcstData['response']['body']['items']['item'][0]['wf4Pm']], 
        ['date' => $now->modify('+1 days')->format('Y-m-d'), 'low_temp' => $TempData['response']['body']['items']['item'][0]['taMin5'], 'high_temp' => $TempData['response']['body']['items']['item'][0]['taMax5'], 
        'fcstAm' => $FcstData['response']['body']['items']['item'][0]['wf5Am'], 'fcstPm' => $FcstData['response']['body']['items']['item'][0]['wf5Pm']], 
        ['date' => $now->modify('+1 days')->format('Y-m-d'), 'low_temp' => $TempData['response']['body']['items']['item'][0]['taMin6'], 'high_temp' => $TempData['response']['body']['items']['item'][0]['taMax6'], 
        'fcstAm' => $FcstData['response']['body']['items']['item'][0]['wf6Am'], 'fcstPm' => $FcstData['response']['body']['items']['item'][0]['wf6Pm']], 
        ['date' => $now->modify('+1 days')->format('Y-m-d'), 'low_temp' => $TempData['response']['body']['items']['item'][0]['taMin7'], 'high_temp' => $TempData['response']['body']['items']['item'][0]['taMax7'], 
        'fcstAm' => $FcstData['response']['body']['items']['item'][0]['wf7Am'], 'fcstPm' => $FcstData['response']['body']['items']['item'][0]['wf7Pm']], 
    ];
}

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

// 건강 지수 계산 함수
function calculateHealthIndex($pm10, $pm25) {
    if ($pm10 == '정보 없음' || $pm25 == '정보 없음') return ['정보 없음', '정보 없음'];
    if (!is_numeric($pm10) || !is_numeric($pm25)) return ['정보 없음', '정보 없음'];

    $index = max($pm10 / 50, $pm25 / 25) * 100;
    if ($index <= 50) return ['좋음', '외출하기 좋은 날씨입니다.'];
    if ($index <= 100) return ['보통', '장시간 외출 시 마스크 착용을 권장합니다.'];
    if ($index <= 150) return ['나쁨', '가능한 외출을 자제하고, 외출 시 반드시 마스크를 착용하세요.'];
    return ['매우 나쁨', '외출을 자제하고 실내에서 공기청정기를 사용하세요.'];
}

function getPmGrade($value, $type) {
    if ($value === '정보 없음') return ['정보 없음', '#808080'];
    if (!is_numeric($value)) return ['정보 없음', '#808080'];
    
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