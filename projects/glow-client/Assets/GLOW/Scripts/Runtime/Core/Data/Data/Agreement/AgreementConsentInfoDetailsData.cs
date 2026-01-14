using System;
using System.Runtime.Serialization;
using Newtonsoft.Json;

namespace GLOW.Core.Data.Data.Agreement
{
    [Serializable]
    public class AgreementConsentInfoDetailsData
    {
        [DataMember(Name = "consent_type")][JsonProperty("consent_type")]
        public int ConsentType { get; set; }
        [DataMember(Name = "consent_flg")][JsonProperty("consent_flg")]
        public int ConsentFlg { get; set; }
    }
}
