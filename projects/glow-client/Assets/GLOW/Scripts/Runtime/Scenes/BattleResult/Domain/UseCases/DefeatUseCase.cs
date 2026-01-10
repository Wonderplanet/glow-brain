using System.Linq;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Constants.AdventBattle;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Scenes.AdventBattle.Domain.Definition.Service;
using GLOW.Scenes.BattleResult.Domain.Evaluator;
using GLOW.Scenes.BattleResult.Domain.Factory;
using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.PvpTop.Domain.Resolver;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.UseCases
{
    public class DefeatUseCase
    {
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstResultTipsDataRepository MstResultTipsDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IStageService StageService { get; }
        [Inject] IGameService GameService { get; }
        [Inject] IAdventBattleService AdventBattleService { get; }
        [Inject] IPvpService PvpService { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] ISelectedStageEvaluator SelectedStageEvaluator { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        [Inject] IEnemyCountResultModelFactory EnemyCountResultModelFactory { get; }
        [Inject] IPvpSelectedOpponentStatusCacheRepository PvpSelectedOpponentStatusCacheRepository { get; }
        [Inject] IInGamePreferenceRepository InGamePreferenceRepository { get; }
        [Inject] IMstCurrentPvpModelResolver MstCurrentPvpModelResolver { get; }
        [Inject] ILocalNotificationScheduler LocalNotificationScheduler { get; }
        [Inject] IInGameRetryEvaluator InGameRetryEvaluator { get; }

        public async UniTask<DefeatResultModel> Defeat(CancellationToken cancellationToken)
        {
            var selectedStage = SelectedStageEvaluator.GetSelectedStage();
            // セッション終了通知
            switch (selectedStage.InGameType)
            {
                case InGameType.AdventBattle:
                    await AdventBattleService.Abort(cancellationToken, AdventBattleAbortType.Retire);
                    break;
                case InGameType.Pvp:
                    // 現状インゲームでのPvpからDefeatになることはない認識だが念の為
                    var pvpAbortResult = await PvpService.Abort(cancellationToken);
                    UpdateGameFetchOtherModel(pvpAbortResult);
                    break;
                case InGameType.Normal:
                default:
                    await StageService.AbortSession(cancellationToken, StageAbortType.Retire);
                    break;
            }

            var fetchResultModel = await GameService.Fetch(cancellationToken);
            GameManagement.SaveGameFetch(fetchResultModel.FetchModel);

            IMstInGameModel mstInGameModel = selectedStage.InGameType switch
            {
                InGameType.AdventBattle => MstAdventBattleDataRepository.GetMstAdventBattleModel(
                    selectedStage.SelectedMstAdventBattleId),
                InGameType.Pvp => MstCurrentPvpModelResolver.CreateMstPvpBattleModel(selectedStage.SelectedSysPvpSeasonId),
                _ => MstStageDataRepository.GetMstStage(selectedStage.SelectedStageId)
            };

            var resultTips = mstInGameModel.ResultTips;
            if (resultTips.Value == string.Empty)
            {
                var userLevel = GameRepository.GetGameFetch().UserParameterModel.Level;
                var defaultTips = MstResultTipsDataRepository.GetMstResultTipsFirstOrDefault(userLevel);
                resultTips = defaultTips.Tips;

                if (defaultTips.IsEmpty())
                {
                    ApplicationLog.LogError(
                        nameof(DefeatUseCase),
                        ZString.Format("No default result tips found for user level: {0}", userLevel));
                }
            }

            // チュートリアル未達成時に端末保存する
            var isOutPostTutorialCompleted = GameRepository.GetGameFetchOther().UserTutorialFreePartModels
                .Any(m => m.TutorialFunctionName == TutorialFreePartIdDefinitions.OutpostEnhance);
            if (!isOutPostTutorialCompleted)
            {
                PreferenceRepository.ShouldStartOutpostEnhanceTutorial = true;
            }

            // コンティニュー選択フラグを端末保存する
            InGamePreferenceRepository.IsInGameContinueSelecting = InGameContinueSelectingFlag.False;

            var enemyCountResult = EnemyCountResultModelFactory.Create();

            // バトルが終了し不要になったためPVPの対戦相手ステータスをクリア
            PvpSelectedOpponentStatusCacheRepository.ClearOpponentStatus();
            
            var inGameRetryModel = InGameRetryEvaluator.DetermineRetryAvailableFlag();

            return new DefeatResultModel(
                resultTips,
                enemyCountResult,
                inGameRetryModel
            );
        }

        void UpdateGameFetchOtherModel(PvpAbortResultModel model)
        {
            // GameRepositoryからGameFetchOtherModelを取得
            var gameFetchOtherModel = GameRepository.GetGameFetchOther();

            if (model.UserItems.Any())
            {
                var updatedItemModels = gameFetchOtherModel.UserItemModels.Update(model.UserItems);
                // アイテム情報をセット
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
