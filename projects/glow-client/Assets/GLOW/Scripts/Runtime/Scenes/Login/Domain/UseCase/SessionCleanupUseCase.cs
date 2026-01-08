using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Exceptions;
using GLOW.Scenes.AdventBattle.Domain.Definition.Service;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.Login.Domain.UseCase
{
    public class SessionCleanupUseCase
    {
        [Inject] IResumableStateRepository ResumableStateRepository { get; }
        [Inject] IStageService StageService { get; }
        [Inject] IAdventBattleService AdventBattleService { get; }
        [Inject] IPvpService PvpService { get; }
        [Inject] IInGamePreferenceRepository InGamePreferenceRepository { get; }
        [Inject] IGameService GameService { get; }
        [Inject] IGameManagement GameManagement { get; }

        public async UniTask CleanupSession(
            CancellationToken cancellationToken,
            InGameContentType inGameContentType)
        {
            InGamePreferenceRepository.IsInGameContinueSelecting = InGameContinueSelectingFlag.False;
            ResumableStateRepository.Clear();

            try
            {
                await CleanUp(cancellationToken, inGameContentType);
            }
            catch (ContentMaintenanceSessionCleanupFailedException)
            {
                // 万が一重複してcleanupが呼ばれた場合の対応
                // cleanupによってクライアント側更新などは無いため問題なし
                // IContentMaintenanceTransitioner側で遷移を行うためここで拾って無視する
                ApplicationLog.LogError(nameof(SessionCleanupUseCase),"Cleanup already executed. Ignoring duplicate cleanup request.");
            }
            // 更新
            var fetchResultModel = await GameService.Fetch(cancellationToken);
            GameManagement.SaveGameFetch(fetchResultModel.FetchModel);

        }

        async UniTask CleanUp(CancellationToken cancellationToken, InGameContentType inGameContentType)
        {
            switch (inGameContentType)
            {
                case InGameContentType.AdventBattle:
                    await AdventBattleService.Cleanup(cancellationToken);
                    break;
                case InGameContentType.Pvp:
                    await PvpService.Cleanup(cancellationToken);
                    break;
                case InGameContentType.Stage:
                default:
                    await StageService.Cleanup(cancellationToken);
                    break;
            }
        }
    }
}
