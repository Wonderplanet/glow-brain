using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.AdventBattle;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.AdventBattle.Domain.Definition.Service;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.UseCases
{
    public class AbortUseCase
    {
        [Inject] IStageService StageService { get; }
        [Inject] IAdventBattleService AdventBattleService { get; }
        [Inject] IGameService GameService { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] ISelectedStageEvaluator SelectedStageEvaluator { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IPvpService PvpService { get; }
        [Inject] ILocalNotificationScheduler LocalNotificationScheduler { get; }

        public async UniTask Abort(CancellationToken cancellationToken)
        {
            InGameScene.IsBattleOver = BattleOverFlag.True;
            var selectedStageModel = SelectedStageEvaluator.GetSelectedStage();
            switch (selectedStageModel.InGameType)
            {
                case InGameType.AdventBattle:
                    await AbortAdventBattle(cancellationToken, selectedStageModel.SelectedMstAdventBattleId);
                    break;
                case InGameType.Normal:
                    await StageService.AbortSession(cancellationToken, StageAbortType.Retire);
                    break;
                case InGameType.Pvp:
                    await AbortPvp(cancellationToken);
                    break;
            }

            // スタミナやステージ挑戦回数情報等を更新する
            var fetchResultModel = await GameService.Fetch(cancellationToken);
            GameManagement.SaveGameFetch(fetchResultModel.FetchModel);
        }

        async UniTask AbortAdventBattle(CancellationToken cancellationToken, MasterDataId mstAdventBattleId)
        {
            var model = await AdventBattleService.Abort(cancellationToken, AdventBattleAbortType.Retire);
            var gameFetchOther = GameRepository.GetGameFetchOther();

            var updatedGameFetchOther = gameFetchOther with
            {
                AdventBattleRaidTotalScoreModel = new AdventBattleRaidTotalScoreModel(
                    mstAdventBattleId,
                    model.TotalScore)
            };

            GameManagement.SaveGameFetchOther(updatedGameFetchOther);

            // 降臨バトルの通知を更新する
            LocalNotificationScheduler.RefreshRemainAdventBattleCountSchedule();
        }

        async UniTask AbortPvp(CancellationToken cancellationToken)
        {
            var model = await PvpService.Abort(cancellationToken);
            var gameFetchOtherModel = GameRepository.GetGameFetchOther();

            if (model.UserItems.Any())
            {
                // アイテム情報をセット
                var updatedItemModels = gameFetchOtherModel.UserItemModels.Update(model.UserItems);

                // PvP情報をセット
                gameFetchOtherModel = gameFetchOtherModel with
                {
                    UserItemModels = updatedItemModels,
                    UserPvpStatusModel = model.UserPvpStatus
                };
            }
            else
            {
                // PvP情報をセット
                gameFetchOtherModel = gameFetchOtherModel with { UserPvpStatusModel = model.UserPvpStatus };
            }

            // GameManagementを利用してModelを更新
            GameManagement.SaveGameFetchOther(gameFetchOtherModel);

            // ランクマッチの通知を更新する
            LocalNotificationScheduler.RefreshRemainPvPSchedule();
        }
    }
}
