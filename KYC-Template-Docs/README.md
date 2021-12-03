# Veriscope
KYC Template Documentation.

When VASPs are required to share PII as it relates to a crypto transaction, e.g. VASP A to VASP B, a KYC Template is formatted and posted directly to the corresponding VASP, as shown in the following diagram.

![Alt text](images/0-KYC-Template.png "KYC Template Diagram")

When VASP B receives the attestation set by VASP A, they complete the following logic flow while adding data to the KYC Template as shown here:

![Alt text](images/1-KYC-Template-Logic-Flow.png "KYC Template Logic Flow")

When VASP A receives the KYC Template via API_URL from VASP B they complete the following logic flow while adding more data to the KYC Template as shown here:

![Alt text](images/2-KYC-Template-Request.png "KYC Template Request")

**NOTE:** Recover XX Signature functions are provided by:
```
$ node shyft-template-helper.js 
listening on 8090
```

As illustrated above "KYC Template Diagram", VASP A and B pass the template back and forth until the template is complete, i.e. all fields are verified and included.

Below is an example of a completed KYC Template

# KYC Template

| Key      | Value |
| :--- | :--- |
| AttestationHash | 0x5fbdd65733a7648e68dcb2243f0744020338e4e8c8f57f103022cded5c04e7a7 |
| BeneficiaryTAAddress | 0xC0cA43B4848823d5417cAAFB9e8E6704b9d5375c |
| BeneficiaryTAPublicKey | c2d213d585fed2213395e61f99b988f692aec84d38b288d14ffc4a90c879531cd92105bf1336da88ec6bf1f86a74293695cfeaa444dc9fcf2614d201e4a64c02 |
| BeneficiaryTASignature | {"r":"0xa5c3307367600180ebccdada90e9091532f230156a9f42d531817f86925bcf8c","s":"0x166d27232c4bc6b4c4f35c773e77cf4cea6392511fb55a49e1ce5613e986ba17","v":"0x26"} |
| BeneficiaryTASignatureHash | 0x121d699204c341a8731803f38a85123d14f1cca922961f8982deacb109d91fa8 |
| BeneficiaryUserAddress | 0x22bA7B19D0261c6962735CB58bc9512e4A4C632D |
| BeneficiaryUserPublicKey | 71ffe330523564e6562d0d4f395aec21d9b78906e1b54422ca1d319ce212885c08a416c4750717d8d5bd45d19bbafe6391759fc517a3f9dd8e925146dd9b59d8 |
| BeneficiaryUserSignature | {"r":"0xbb57ed1008d45d7db40e988a9c25f8df7da2dd02dc5a2f7cba0b619f8e356018","s":"0x2a24d30cc7b11d2f47d147fbfc715924d2e20ff9f6542cbde2b51ee62c24fba4","v":"0x25"} |
| BeneficiaryUserSignatureHash | 0x5377021d9f5310d1477d1a7eee6750941ceb7ba2922e6f4309237355eef4ab68 |
| CryptoAddressType | ETH |
| CryptoAddress | 0x3790EDF76F21d698ba546f0a3a9C7F479b49F39E |
| OriginatorTAAddress | 0x875514ec00250489fA1f0aF121Af539541fbcf25 |
| OriginatorTAPublicKey | bc67701eaa5920c127a6d2071143e84028d734f608e4afc8147e9a54d4de9cc088d1324f6ba9ba1341f427c39cb975d9470b1793f2ff2679c413fbe2e269c65b |
| OriginatorTASignature | {"r":"0x4a577009a52382c20512df679690520b2f9b167ace24705a20fdc0e2b4598e02","s":"0x1b891d51991a5084d5c2ad9480c96dda03338e0aeeecbf6598399d54358ab4de","v":"0x26"} |
| OriginatorTASignatureHash | 0x121d699204c341a8731803f38a85123d14f1cca922961f8982deacb109d91fa8 |
| OriginatorUserAddress | 0x0a32BA5d01495a007D9B87DD85b22d8D6b803F23 |
| OriginatorUserPublicKey | 7cbf90228a369c9e97c3ba21a70ba09f4ad9e6322a346390de0c0595c33f09e2df8aa30c19190b98272a3d39306e5b73b3a98437c51eb6c5c7fbc7b885d22292 |
| OriginatorUserSignature | {"r":"0x78633ba8e0aaf5a4dd382a995d87e230d893207d4b9459734677893c0d55aa96","s":"0x55cbdc6936b7490d7dc2963cb575393ae56f6e1630fea9a5af13efcea148ae9d","v":"0x26"} |
| OriginatorUserSignatureHash | 0x5377021d9f5310d1477d1a7eee6750941ceb7ba2922e6f4309237355eef4ab68 |
| BeneficiaryKyc | i2VMIgHljRmmPWkBBPD0ZgSgGXM43BmSDhG1kIwD9iP8y/gsZmX3RUWXmCb97UAoYIV3EMmUqt85DveRT9Ei2KkC8xiazbHsOcPG4/U466d/QDJIXr+EyB4SP/QnPXUyEwnKHhtdRmIQC/IgnqHXKU0HyB4feo5z06ubZ4ZaY2Pk+SyL8VyEv/OEzy/w1l5C/y+hC6I4fmAeQIg8MPQuPuokg9Cwmdt6RotrzCwPDZDcZboWaLnpq38wQK+rrC7AUXwr3AXxZ7F+0v |
| OriginatorKyc | JVH3GUML9ciWACdzJlSj2ASXAa57GTcnbdcB3bjg9UzQg0lQKFtAtRLjJOjbbojzLTVY8/wNK3U3m8iCialzTh2+ihSunqLEAzQfy25QjHzf+CQeUgKcl7wugHBe+bbCJklhrhq2W9KChYVNyZJUzoxwxjztD0YXWjInJ1nAwa3BZFkP9dUG0liZb7J5w4l9TziF80M1LSl6cHqyfOqrgljbWWO9ln5c3gqj4L7PqK8nv7MsW70uQzG0c9UEwxhRgg== |
| BeneficiaryTAUrl | https://pcf.veriscope.network/kyc-template |
| OriginatorTAUrl | https://veriscope.coinesto.com/kyc-template |
| BeneficiaryKycDecrypt | {"fullname":"Felix Bailey","dob":"1986-04-14 14:44:28","jurisdiction":120} |
| OriginatorKycDecrypt | {"fullname":"Alice","dob":"2000-02-04 17:00:00","jurisdiction":196} |


**NOTE:** Future additions to the KYC Template will include Crypto Network, Crypto TXN Hash, Crypto Value to record actual crypto transactions as they relate to attestations.

