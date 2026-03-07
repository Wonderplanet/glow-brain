using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.TutorialTapIcon.Presentation.ValueObject;
using GLOW.Modules.TutorialTipDialog.Presentation.View;
using GLOW.Scenes.UnitTab.Presentation.Views;

namespace GLOW.Modules.Tutorial.Presentation.Sequence.FreePart
{
    public class ReleaseArtworkEffectTutorialSequence : BaseTutorialSequence
    {
        public override async UniTask Play(CancellationToken cancellationToken)
        {
            // チュートリアルシーケンス開始
            await FadeInGrayOut(cancellationToken);

            await ShowTutorialText(cancellationToken, "原画編成が\n出来るようになったぞ！", 0f);

            ShowArrowIndicator("home_footer_unit_button", ReverseFlag.False);
            await ShowTutorialText(cancellationToken, "編成画面から編成しよう！", 0f);
            HideArrowIndicator();
            await HideTutorialText(cancellationToken);

            // チュートリアル進捗更新
            await CompleteFreePartTutorial(cancellationToken, TutorialFreePartIdDefinitions.ReleaseArtworkEffect);

            // 終了
            await FadeOutGrayOut(cancellationToken);
        }
    }
}
