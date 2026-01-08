using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaContent.Domain.Calculator;
using GLOW.Scenes.GachaResult.Domain.Model;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.GachaResult.Domain.UseCases
{
    public class TutorialGachaReDrawUseCase
    {
        [Inject] IOprGachaRepository OprGachaRepository { get; }
        [Inject] IOprGachaUseResourceRepository OprGachaUseResourceRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }

        // チュートリアルガシャを引くために必要な情報を取得 //NOTE:API疎通時に不要になる可能性があります
        public TutorialGachaReDrawUseCaseModel GetTutorialGachaReDrawUseCaseModel(MasterDataId gachaId, GachaDrawType gachaDrawType)
        {
            var gameFetchModel = GameRepository.GetGameFetch();
            var gameFetchOtherModel = GameRepository.GetGameFetchOther();
            var oprGachaModel = OprGachaRepository.GetOprGachaModelFirstOrDefaultById(gachaId);
            var gachaUseResourceModels = OprGachaUseResourceRepository.FindByGachaId(oprGachaModel.GachaId);
            var oprGachaUseResourceModel = OprGachaUseResourceModel.Empty;

            switch (gachaDrawType)
            {
                case GachaDrawType.Single:
                    var singleUseResourceModels = gachaUseResourceModels
                        .Where(model => model.GachaDrawCount.Value == 1 && model.CostType != CostType.Ad)
                        .ToList();

                    oprGachaUseResourceModel = GachaContentCalculator.GetHighestPriorityUseResourceModel(
                        singleUseResourceModels,
                        gameFetchModel,
                        gameFetchOtherModel,
                        SystemInfoProvider.GetApplicationSystemInfo().PlatformId);
                    break;

                case GachaDrawType.Multi:

                    var multiUseResourceModels = gachaUseResourceModels
                        .Where(model => model.GachaDrawCount.Value > 1 && model.CostType != CostType.Ad)
                        .OrderByDescending(model => model.GachaCostPriority)
                        .ToList();
                    // 消費リソース取得 (消費リソースがない場合はプライオリティが低いものを返す)
                    oprGachaUseResourceModel = GachaContentCalculator.GetHighestPriorityUseResourceModel(
                        multiUseResourceModels,
                        gameFetchModel,
                        gameFetchOtherModel,
                        SystemInfoProvider.GetApplicationSystemInfo().PlatformId);
                    break;
            }

            var costType = oprGachaUseResourceModel.CostType;
            var costAmount = oprGachaUseResourceModel.CostAmount;
            var gachaDrawCount = oprGachaUseResourceModel.GachaDrawCount;

            return new TutorialGachaReDrawUseCaseModel(
                oprGachaModel.GachaId,
                oprGachaModel.GachaType,
                gachaDrawCount,
                costType,
                costAmount,
                oprGachaUseResourceModel.MstCostId
                );
        }
    }
}
