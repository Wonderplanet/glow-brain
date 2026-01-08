using System.Linq;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Extensions;
using GLOW.Scenes.AnnouncementWindow.Domain.Model;
using GLOW.Scenes.GachaDetailDialog.Domain.Models;
using Zenject;

namespace GLOW.Scenes.GachaDetailDialog.Domain.UseCases
{
    public class GachaDetailDialogUseCase
    {
        const string GachaCautionContentsUrlFormat = "gacha_caution/html/{0}.html";
        
        [Inject] IOprGachaRepository OprGachaRepository { get; }
        [Inject] IAnnouncementCacheRepository AnnouncementCacheRepository { get; }
        [Inject] IAnnouncementService AnnouncementService { get; }
        [Inject] IAnnouncementCellUseCaseModelFactory AnnouncementCellUseCaseModelFactory { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public async UniTask<GachaDetailDialogUseCaseModel> GetGachaDetailUseCaseModel(
            CancellationToken cancellationToken, 
            MasterDataId oprGachaId)
        {
            var oprGachaModel = OprGachaRepository.GetOprGachaModelFirstOrDefaultById(oprGachaId);
            var informationId = GetAnnouncementId(oprGachaModel);
            var gachaCautionId = GetGachaCautionId(oprGachaModel);
            
            var announcementContentsUrl = await GetAndTryCacheAnnouncement(informationId, cancellationToken);
            var gachaAttentionContentsUrl = ToGachaCautionContentsUrl(gachaCautionId);
            
            return new GachaDetailDialogUseCaseModel(
                announcementContentsUrl,
                gachaAttentionContentsUrl);
        }

        AnnouncementId GetAnnouncementId(OprGachaModel oprGachaModel)
        {
            if (oprGachaModel.AnnouncementId.IsEmpty()) return AnnouncementId.Empty;

            return oprGachaModel.AnnouncementId;
        }
        
        GachaCautionId GetGachaCautionId(OprGachaModel oprGachaModel)
        {
            if (oprGachaModel.GachaCautionId.IsEmpty()) return GachaCautionId.Empty;
            
            return oprGachaModel.GachaCautionId;
        }

        async UniTask<AnnouncementContentsUrl> GetAndTryCacheAnnouncement(
            AnnouncementId gachaInformationId, 
            CancellationToken ct)
        {
            if(gachaInformationId.IsEmpty()) return AnnouncementContentsUrl.Empty;

            var announcement = AnnouncementCacheRepository.Get(gachaInformationId);
            if(!announcement.IsEmpty()) return announcement.AnnouncementContentsUrl;

            // お知らせがあり、キャッシュしていない場合はお知らせを取得
            var result = await AnnouncementService.Index(ct, TimeProvider.Now);
            var useCaseModel = result.AnnouncementModels.Select(AnnouncementCellUseCaseModelFactory.Create).ToList();
            var readAnnouncementDictionary = useCaseModel.ToDictionary(model => model.AnnouncementId, model => model);
            
            AnnouncementCacheRepository.SetReadAnnouncementDictionary(readAnnouncementDictionary);

            var gachaAnnouncementModel = useCaseModel.FirstOrDefault(
                x => x.AnnouncementId == gachaInformationId,
                AnnouncementCellUseCaseModel.Empty);
            
            return gachaAnnouncementModel.AnnouncementContentsUrl;
        }

        GachaCautionContentsUrl ToGachaCautionContentsUrl(GachaCautionId gachaCautionId)
        {
            if (gachaCautionId.IsEmpty()) return GachaCautionContentsUrl.Empty;
            
            var formatUrl = ZString.Format(GachaCautionContentsUrlFormat, gachaCautionId.Value);
            var gachaCautionUrl = new GachaCautionContentsUrl(formatUrl);

            return gachaCautionUrl;
        }
    }
}
