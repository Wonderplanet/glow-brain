using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.DataStores.Announcement;
using GLOW.Core.Data.Translators;
using GLOW.Core.Domain.Models.Announcement;
using GLOW.Core.Domain.Services;
using UnityHTTPLibrary;
using WPFramework.Exceptions.Mappers;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public class AnnouncementService : IAnnouncementService
    {
        [Inject] AnnouncementApi InformationApi { get; }
        
        [Inject] IServerErrorExceptionMapper ServerErrorExceptionMapper { get; }
        
        async UniTask<AnnouncementResultModel> IAnnouncementService.Index(CancellationToken cancellationToken, DateTimeOffset nowTime)
        {
            try
            {
                var result = await InformationApi.Index(cancellationToken, GetDateTimeOffsetUrl(nowTime));
                return AnnouncementResultModelTranslator.ToAnnouncementResultModel(result);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<AnnouncementLastUpdatedModel> IAnnouncementService.LastUpdated(CancellationToken cancellationToken, DateTimeOffset nowTime)
        {
            try
            {
                var result = await InformationApi.LastUpdated(cancellationToken, GetDateTimeOffsetUrl(nowTime));
                return AnnouncementLastUpdatedModelTranslator.ToAnnouncementLastUpdatedModel(result);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }
        
        /// <summary>
        /// DateTimeOffsetをISO 8601に則った文字列に変換する
        /// </summary>
        /// <param name="dateTime">サーバーから取得した時刻想定</param>
        /// <returns></returns>
        string GetDateTimeOffsetUrl(DateTimeOffset? dateTime)
        {
            if (dateTime.HasValue)
            {
                var dateTimeString = dateTime.Value.ToString("yyyy-MM-ddTHH:mm:sszzz");
                return "?now=" + dateTimeString.Replace("+", "%2B");
            }

            return string.Empty;
        }
    }
}