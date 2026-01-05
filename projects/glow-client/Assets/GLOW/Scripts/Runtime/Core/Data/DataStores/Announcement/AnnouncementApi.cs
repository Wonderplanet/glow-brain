using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.Data.Announcement;
using UnityHTTPLibrary;
using WPFramework.Data.Extensions;
using Zenject;

namespace GLOW.Core.Data.DataStores.Announcement
{
    public sealed class AnnouncementApi
    {
        [Inject(Id = WPFramework.Constants.Zenject.FrameworkInjectId.ServerApi.Announcement)] ServerApi APIContext { get; }
        
        public async UniTask<AnnouncementResultData> Index(CancellationToken cancellationToken, string nowTime)
        {
            var payload = new Payload()
            {
                Data = Array.Empty<byte>(),
                ContentType = MimeTypes.Json
            };
            
            var path = "/information/index" + nowTime;
            return await APIContext.Get<AnnouncementResultData>(cancellationToken, path, payload);
        }
        
        public async UniTask<AnnouncementLastUpdatedData> LastUpdated(CancellationToken cancellationToken, string nowTime)
        {

            var payload = new Payload()
            {
                Data = Array.Empty<byte>(),
                ContentType = MimeTypes.Json
            };
            
            var path = "/information/last-updated" + nowTime;
            return await APIContext.Get<AnnouncementLastUpdatedData>(cancellationToken, path, payload);
        }
    }
}