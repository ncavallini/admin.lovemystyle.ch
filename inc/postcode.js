async function fetchCityFromPostCode(postcode) {
    const response = await fetch(`https://service.post.ch/zopa/app/api/addresschecker/v1/zips?zip=${postcode}`);
    const data = await response.json();
    if(!data.zip || !data.zip.length) {
        return "";
    }
    return data.zip[0].city27;
}