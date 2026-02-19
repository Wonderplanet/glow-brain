using System;
using System.Runtime.Serialization;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.Data.Agreement;
using Newtonsoft.Json;
using UnityEngine;
using UnityHTTPLibrary;
using WPFramework.Data.Extensions;
using Zenject;

namespace GLOW.Core.Data.DataStores.Agreement
{
    public sealed class AgreementApi
    {
        [Serializable]
        class JsonParamAgreement
        {
            [IgnoreDataMember] [JsonIgnore]
            public string UserId
            {
                get => userId;
                set => userId = value;
            }
            [IgnoreDataMember] [JsonIgnore]
            public string Lang
            {
                get => lang;
                set => lang = value;
            }
            [IgnoreDataMember] [JsonIgnore]
            public string CallbackUrl
            {
                get => callbackUrl;
                set => callbackUrl = value;
            }
            [IgnoreDataMember] [JsonIgnore]
            public int BnLogo
            {
                get => bnLogo;
                set => bnLogo = value;
            }
            [IgnoreDataMember] [JsonIgnore]
            public int[] ConsentTypes
            {
                get => consentTypes;
                set => consentTypes = value;
            }

            [DataMember(Name = "user_id")] [SerializeField] [JsonProperty("user_id")] string userId;
            [DataMember(Name = "lang")] [SerializeField] [JsonProperty("lang")] string lang;
            [DataMember(Name = "callback_url")] [SerializeField] [JsonProperty("callback_url")] string callbackUrl;
            [DataMember(Name = "bn_logo")] [SerializeField] [JsonProperty("bn_logo")] int bnLogo;
            [DataMember(Name = "consent_types")] [SerializeField] [JsonProperty("consent_types")] int[] consentTypes;
        }

        [Inject(Id = WPFramework.Constants.Zenject.FrameworkInjectId.ServerApi.Agreement)] ServerApi APIContext { get; }

        public async UniTask<AgreementConsentInfosData> ConsentInfos(
            CancellationToken cancellationToken,
            string userId)
        {
            var payload = new Payload()
            {
                Data = Array.Empty<byte>(),
                ContentType = MimeTypes.Json
            };
            var uriParameters = System.Web.HttpUtility.ParseQueryString(string.Empty);
            uriParameters["user_id"] = userId;

            return await APIContext.Get<AgreementConsentInfosData>(cancellationToken, "/api/v1/consent_infos?" + uriParameters, payload);
        }

        public async UniTask<AgreementConsentRequestData> ConsentRequest(
            CancellationToken cancellationToken,
            string userId,
            string lang,
            string callbackUrl,
            int bnLogo,
            int[] consentTypes)
        {
            var param = new JsonParamAgreement()
            {
                UserId = userId,
                Lang = lang,
                CallbackUrl = callbackUrl,
                BnLogo = bnLogo,
                ConsentTypes = consentTypes
            };
            var json = JsonConvert.SerializeObject(param);
            var payload = new Payload()
            {
                Data = System.Text.Encoding.UTF8.GetBytes(json),
                ContentType = MimeTypes.Json
            };

            return await APIContext.Post<AgreementConsentRequestData>(cancellationToken, "/api/v1/consent/request", payload);
        }
    }
}
