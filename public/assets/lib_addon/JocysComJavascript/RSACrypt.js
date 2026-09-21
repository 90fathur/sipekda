// RSA
function GetNewRsaProvider(dwKeySize) {
    // Create a new instance of RSACryptoServiceProvider.
    if (!dwKeySize) dwKeySize = 1024;
    return new System.Security.Cryptography.RSACryptoServiceProvider(dwKeySize);
}

function RSAEncrypt(text, publicKey) {            
    // ------------------------------------------------
    // Encrypt
    // ------------------------------------------------
    var decryptedBytes = System.Text.Encoding.UTF8.GetBytes(text);
    var rsa = GetNewRsaProvider();

    rsa.FromXmlString(publicKey);
    var encryptedData = rsa.Encrypt(decryptedBytes, true);
    var base64Encrypted = System.Convert.ToBase64String(encryptedData);

    return base64Encrypted;
}