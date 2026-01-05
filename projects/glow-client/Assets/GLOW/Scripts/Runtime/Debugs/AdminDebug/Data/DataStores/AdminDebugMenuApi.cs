using System;
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

        public async UniTask<HeadOK> DebugCommandExecute(CancellationToken cancellationToken, string command)
        {
            var param = new JsonParamExecute();
            param.Command = command;
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
