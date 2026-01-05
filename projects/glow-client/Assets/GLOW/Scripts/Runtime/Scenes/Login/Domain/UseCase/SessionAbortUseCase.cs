using System.Threading;
using System.Linq;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.AdventBattle;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.AdventBattle.Domain.Definition.Service;
using Zenject;

namespace GLOW.Scenes.Login.Domain.UseCase
{
    public class SessionAbortUseCase
    {
        [Inject] IResumableStateRepository ResumableStateRepository { get; }
        [Inject] IStageService StageService { get; }
        [Inject] IAdventBattleService AdventBattleService { get; }
        [Inject] IPvpService PvpService { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IInGamePreferenceRepository InGamePreferenceRepository { get; }
        [Inject] ILocalNotificationScheduler LocalNotificationScheduler { get; }

        public async UniTask OnAbortSession(CancellationToken cancellationToken, InGameContentType inGameContentType)
        {
            InGamePreferenceRepository.IsInGameContinueSelecting = InGameContinueSelectingFlag.False;
            ResumableStateRepository.Clear();
            switch (inGameContentType)
            {
                case InGameContentType.AdventBattle:
                    await AdventBattleService.Abort(cancellationToken, AdventBattleAbortType.CancelResume);
                    break;
                case InGameContentType.Pvp:
                    var model = await PvpService.Abort(cancellationToken);
                    UpdateGameFetchOtherModel(model);
                    break;
                case InGameContentType.Stage:
                default:
                    await StageService.AbortSession(cancellationToken, StageAbortType.CancelResume);
                    break;
            }
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
