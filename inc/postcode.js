async function getCityFromPostCode(cityInput, postcode, country = "CH") {
    const response = await fetch(`https://api.zippopotam.us/${country}/${postcode}`);
    
    if (!response.ok) {
        console.error("Error fetching data from Zippopotam API:", response.statusText);
        return;
    }
    
    const data = await response.json();
    
    if (data.places && data.places.length > 0) {
        const city = data.places.at(-1)["place name"];
        cityInput.value = city;
    } else {
        console.warn("No city found for the provided postcode.");
    }
}

async function getPostCodeFromCity(postcodeInput, city, country = "CH") {
    const response = await fetch(`https://api.zippopotam.us/${country}/${city}`);
    
    if (!response.ok) {
        console.error("Error fetching data from Zippopotam API:", response.statusText);
        return;
    }
    
    const data = await response.json();
    
    if (data.places && data.places.length > 0) {
        const postcode = data.places.at(0)["post code"];
        postcodeInput.value = postcode;
    } else {
        console.warn("No postcode found for the provided city.");
    }
}