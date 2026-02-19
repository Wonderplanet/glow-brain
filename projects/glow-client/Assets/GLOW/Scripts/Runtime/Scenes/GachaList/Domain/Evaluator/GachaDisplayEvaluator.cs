using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Extensions;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.GachaList.Domain.Evaluator
{
    public class GachaDisplayEvaluator : IGachaDisplayEvaluator
    {
        [Inject] IGachaEvaluator GachaEvaluator { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IOprGachaUseResourceRepository OprGachaUseResourceRepository { get; }

        bool IGachaDisplayEvaluator.ShouldShowDisplay(OprGachaModel oprGachaModel)
        {
            var userGachaModel = GameRepository.GetGameFetchOther().UserGachaModels
                .FirstOrDefault(mode => mode.OprGachaId == oprGachaModel.Id, UserGachaModel.CreateById(oprGachaModel.Id));
            var gameFetchModel = GameRepository.GetGameFetch();
            var gameFetchOtherModel = GameRepository.GetGameFetchOther();
            var gachaUseResourceModels = OprGachaUseResourceRepository.FindByGachaId(oprGachaModel.Id);

            // 開放条件を持ち、条件を満たしていない場合は表示しない
            if (!MeetsUnlockCondition(oprGachaModel, gameFetchOtherModel)) return false;

            // 開放期間をもち、開放期間外の場合は表示しない
            if (GachaEvaluator.IsExpiredUnlockDuration(
                    oprGachaModel.UnlockDurationHours,
                    userGachaModel.GachaExpireAt,
                    TimeProvider.Now))
            {
                return false;
            }

            // チケット/メダルガシャで未所持時非表示設定の場合表示しない
            if ((oprGachaModel.GachaType == GachaType.Ticket || oprGachaModel.GachaType == GachaType.Medal) &&
                oprGachaModel.AppearanceCondition == AppearanceCondition.HasTicket &&
                !HasResourceItem(
                    oprGachaModel,
                    gachaUseResourceModels,
                    userGachaModel,
                    gameFetchModel,
                    gameFetchOtherModel))
            {
                return false;
            }

            // 有償限定ガチャかつ上限まで引いている場合は表示しない
            if (oprGachaModel.GachaType == GachaType.PaidOnly &&
                GachaEvaluator.HasReachedDrawLimitedCount(oprGachaModel, userGachaModel))
            {
                return false;
            }

            return true;

        }


        bool HasResourceItem(
            OprGachaModel oprGachaModel,
            IReadOnlyList<OprGachaUseResourceModel> gachaUseResourceModels,
            UserGachaModel userGachaModel,
            GameFetchModel gameFetchModel,
            GameFetchOtherModel gameFetchOtherModel)
        {
            var platformId = SystemInfoProvider.GetApplicationSystemInfo().PlatformId;
            foreach (var useResourceModel in gachaUseResourceModels)
            {
                DrawableFlag drawableFlag = GachaEvaluator.IsGachaDrawable(
                    useResourceModel,
                    gameFetchModel,
                    gameFetchOtherModel,
                    platformId,
                    oprGachaModel,
                    userGachaModel
                );

                if (drawableFlag.Value)
                {
                    return true;
                }
            }
            return false;
        }


        bool MeetsUnlockCondition(OprGachaModel oprGachaModel, GameFetchOtherModel gameFetchOtherModel)
        {
            // 条件がない場合は表示する
            if(oprGachaModel.UnlockConditionType == GachaUnlockConditionType.None) return true;

            // チュートリアルメインパート完了済みの場合は表示する
            if (oprGachaModel.UnlockConditionType == GachaUnlockConditionType.MainPartTutorialComplete &&
                gameFetchOtherModel.TutorialStatus.IsCompleted())
            {
                return true;
            }

            return false;
        }

    }
}
