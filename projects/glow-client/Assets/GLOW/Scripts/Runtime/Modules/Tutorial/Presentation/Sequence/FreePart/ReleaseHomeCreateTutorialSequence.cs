using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.TutorialTapIcon.Presentation.ValueObject;

namespace GLOW.Modules.Tutorial.Presentation.Sequence.FreePart
{
    public class ReleaseHomeCreateTutorialSequence : BaseTutorialSequence
    {
        public override async UniTask Play(CancellationToken cancellationToken)
        {
            // チュートリアルシーケンス開始
            await FadeInGrayOut(cancellationToken);

            await ShowTutorialText(cancellationToken, "ホームクリエイトができるようになったぞ！", 0f);

            ShowArrowIndicator("home_main_koma_setting", ReverseFlag.False);

            await ShowTutorialText(cancellationToken, "ホームクリエイトするときは\nこのアイコンをタップしよう！", 0f);
            HideArrowIndicator();
            await HideTutorialText(cancellationToken);

            // チュートリアル進捗更新
            await CompleteFreePartTutorial(cancellationToken, TutorialFreePartIdDefinitions.ReleaseHomeCreate);

            // 終了
            await FadeOutGrayOut(cancellationToken);
        }
    }
}
