using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaList.Domain.Applier;
using GLOW.Scenes.GachaList.Domain.Definition.Service;
using Zenject;

namespace GLOW.Scenes.GachaList.Domain.UseCases
{
    public class GachaDrawUseCase
    {
        [Inject] IGachaService GachaService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGachaCacheRepository GachaCacheRepository { get; }
        [Inject] IGachaDrawResultApplier GachaDrawResultApplier { get; }
        //ガチャをひく
        public async UniTask GachaDraw(
            CancellationToken cancellationToken,
            MasterDataId gachaId,
            GachaType gachaType,
            GachaDrawCount gachaDrawCount,
            CostType costType,
            GachaDrawType gachaDrawType,
            CostAmount costAmount,
            MasterDataId costId)
        {
            // ガチャを引く前の情報
            var preFetchOtherModel = GameRepository.GetGameFetchOther();
            GachaPlayedCount playedCount = preFetchOtherModel.UserGachaModels.FirstOrDefault(model=>model.OprGachaId == gachaId)?.PlayedCount ?? GachaPlayedCount.Zero;
            GachaDrawResultModel resultModel;
            switch (costType)
            {
                case CostType.Ad:
                    resultModel = await GachaService.DrawByAd(
                        cancellationToken: cancellationToken,
                        oprGachaId: gachaId,
                        playedCount: playedCount
                    );
                    break;
                case CostType.Item:
                    resultModel = await GachaService.DrawByItem(
                        cancellationToken: cancellationToken,
                        oprGachaId: gachaId,
                        drawCount: gachaDrawCount,
                        playedCount: playedCount,
                        costId: costId,
                        costAmount: costAmount
                    );
                    break;
                case CostType.Diamond:
                    resultModel = await GachaService.DrawByDiamond(
                        cancellationToken: cancellationToken,
                        oprGachaId: gachaId,
                        drawCount: gachaDrawCount,
                        playedCount: playedCount,
                        costAmount: costAmount
                    );
                    break;
                case CostType.PaidDiamond:
                    resultModel = await GachaService.DrawByPaidDiamond(
                        cancellationToken: cancellationToken,
                        oprGachaId: gachaId,
                        drawCount: gachaDrawCount,
                        playedCount: playedCount,
                        costAmount: costAmount
                    );
                    break;
                case CostType.Free:
                    resultModel = await GachaService.DrawByFree(
                        cancellationToken: cancellationToken,
                        oprGachaId: gachaId,
                        playedCount: playedCount
                    );
                    break;
                    default:
                        // エラーを出す
                        return;
            }

            // 結果をリポジトリ保存
            GachaCacheRepository.SaveGachaResultModels(resultModel.GachaResultModels.ToList());

            // 連続で引くようにするために、引いたガチャの情報を保存
            var drawTypeModel = new GachaDrawInfoModel(gachaId, gachaType, gachaDrawType, costType);
            GachaCacheRepository.SaveGachaDrawType(drawTypeModel);

            GachaDrawResultApplier.UpdateGachaResult(resultModel);
        }
    }
}
