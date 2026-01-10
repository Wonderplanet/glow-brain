using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Presentation.Manager;

namespace GLOW.Scenes.InGame.Presentation.Views
{
    public interface ITutorialInGameViewDelegate
    {
        void TutorialPauseInGame();
        void TutorialResumeInGame();
        void TutorialTransitionToHome();
        void SetFullRushGauge();
        void SetSummonCostToZero();
        void SetOneUnitSummonCostToZero();
        void SetFirstUnitSpecialAttackCoolTimeToZero();
        void SkipTutorial();
        UniTask AwaitStartInGame(CancellationToken cancellationToken);
        void SetPlayingTutorialFlag(bool isPlayingTutorial);
        void SetPlayingUnitDetailTutorialFlag(bool isPlayingUnitDetailTutorial);
        TutorialIntroductionMangaManager GetTutorialIntroductionMangaManager();
        bool IsEndTutorial {get;}
        void DisableRushAnimSkip();
    }
}
