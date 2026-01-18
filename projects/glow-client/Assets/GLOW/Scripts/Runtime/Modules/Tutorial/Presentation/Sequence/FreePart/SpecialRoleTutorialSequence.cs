using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Scenes.UnitTab.Presentation.Views;

namespace GLOW.Modules.Tutorial.Presentation.Sequence.FreePart
{
    public class SpecialRoleTutorialSequence : BaseTutorialSequence
    {
        public override async UniTask Play(CancellationToken cancellationToken)
        {
            // チュートリアルシーケンス開始
            await FadeInGrayOut(cancellationToken);

            await ShowTutorialText(cancellationToken, "「スペシャルロール」の\nキャラを獲得したね！", 0f);
            await HideTutorialText(cancellationToken);

            ShowIndicator("home_footer_unit_button");
            await WaitClickEvent(cancellationToken, "home_footer_unit_button");
            HideIndicator();
            await WaitViewPresentation<UnitTabViewController>(cancellationToken);

            // ユニット一覧画面
            ShowIndicator("party_formation_button");
            await WaitClickEvent(cancellationToken, "party_formation_button");
            HideIndicator();

            // 画面切り替え待ち
            await UniTask.Delay(300, cancellationToken: cancellationToken);

            // 編成画面
            await ShowTutorialText(cancellationToken, "スペシャルロールのキャラは\nパーティに入れてるだけで\n「JUMBLE RUSH」のダメージが\nアップするんだ！", 0f);
            await ShowTutorialText(cancellationToken, "このロールは直接戦わないから\n必ず他のロールのキャラと一緒に\nパーティに入れてね！\n", 0f);
            await HideTutorialText(cancellationToken);

            // 終了
            await FadeOutGrayOut(cancellationToken);

            // チュートリアル進捗更新
            await CompleteFreePartTutorial(cancellationToken, TutorialFreePartIdDefinitions.SpecialUnit);
        }
    }
}