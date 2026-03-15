using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.Tutorial.Presentation.Views;
using GLOW.Scenes.PvpTop.Presentation;
using GLOW.Scenes.QuestContentTop.Domain.enums;
using GLOW.Scenes.QuestContentTop.Presentation;

namespace GLOW.Modules.Tutorial.Presentation.Sequence.FreePart
{
    public class ReleasePvpTutorialSequence : BaseTutorialSequence
    {

        public override async UniTask Play(CancellationToken cancellationToken)
        {
            // チュートリアルシーケンス開始
            await FadeInGrayOut(cancellationToken);

            // ホーム画面
            await ShowTutorialText(cancellationToken, "ランクマッチが開催中だよ！", 0f);
            await HideTutorialText(cancellationToken);

            ShowIndicator("home_main_pvp_button");
            await WaitClickEvent(cancellationToken, "home_main_pvp_button");
            HideIndicator();

            // チュートリアル進捗更新
            await CompleteFreePartTutorial(cancellationToken, TutorialFreePartIdDefinitions.ReleasePvp);

            await WaitViewPresentation<PvpTopViewController>(cancellationToken);

            // 初遷移時のダイアログ表示はTransitPvpTutorialで行う


            // 終了
            await FadeOutGrayOut(cancellationToken);
        }
    }
}
