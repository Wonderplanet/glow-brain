using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Threading;
using Cysharp.Threading.Tasks;
using Newtonsoft.Json;
using UnityEngine;
using UnityHTTPLibrary;
using WPFramework.Data.Extensions;
using Zenject;

namespace GLOW.Debugs.AdminDebug.Data.DataStores
{
    public sealed class AdminDebugMenuApi
    {
        [Serializable]
        class JsonParamList
        {
        }
        [Serializable]
        class JsonParamExecute
        {
            [IgnoreDataMember] [JsonIgnore]
            public string Command {
                get => command;
                set => command = value;
            }
            [DataMember(Name = "command")] [SerializeField] [JsonProperty("command")] string command;

            [IgnoreDataMember] [JsonIgnore]
            public Dictionary<string, object> Params {
                get => @params;
                set => @params = value;
            }
            [DataMember(Name = "params")] [JsonProperty("params")] Dictionary<string, object> @params;
        }

        [Inject(Id = WPFramework.Constants.Zenject.FrameworkInjectId.ServerApi.Game)] ServerApi APIContext { get; }

        public async UniTask<AdminDebugMenuListResultData> DebugCommandList(CancellationToken cancellationToken)
        {
            var payload = new Payload()
            {
                Data = Array.Empty<byte>(),
                ContentType = MimeTypes.Json
            };

            return await APIContext.Get<AdminDebugMenuListResultData>(cancellationToken, "/api/debug_command/list", payload);
        }

        public async UniTask<HeadOK> DebugCommandExecute(
            CancellationToken cancellationToken,
            string command,
            Dictionary<string, object> parameters = null)
        {
            var param = new JsonParamExecute();
            param.Command = command;
            param.Params = parameters ?? new Dictionary<string, object>();
            var json = JsonConvert.SerializeObject(param);
            var payload = new Payload()
            {
                Data = System.Text.Encoding.UTF8.GetBytes(json),
                ContentType = MimeTypes.Json
            };

            return await APIContext.Post<HeadOK>(cancellationToken, "/api/debug_command/execute", payload);
        }
    }
}
