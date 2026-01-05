using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.TutorialTipDialog.Presentation.View;
using GLOW.Scenes.InGame.Presentation.Components;
using UnityEngine.UI;
using WPFramework.Presentation.Components;
using Zenject;

namespace GLOW.Modules.Tutorial.Presentation.Sequence
{
    /// <summary>
    /// メインパートチュートリアル
    /// チュートリアルステージ3 チュートリアルシーケンス
    /// </summary>

    public class InGame2TutorialSequence : BaseInGameTutorialSequence
    {
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        
        public override async UniTask Play(CancellationToken cancellationToken)
        {
            // パーティメンバー数を取得
            var partyCount = PartyCacheRepository.GetCurrentPartyModel()
                .GetUnitList()
                .Count(u => !u.IsEmpty());
            
            var tipModels = GetTutorialTips(new MasterDataId("InGame2"));
            
            var footerComponent = FindTargetObject("footer_component").GetComponent<InGameFooterComponent>();
            footerComponent.ShowRushButton();
            
            var rushGauge = FindTargetObject("rush_gauge");
            var rushGaugeButton = rushGauge.GetComponent<Button>();
            rushGaugeButton.enabled = false;
            
            for (var i = 1; i <= partyCount; i++)
            {
                FindTargetObject("chara_button_" + i).GetComponent<Button>().enabled = false;
                FindTargetObject("chara_button_" + i).GetComponent<UIButtonLongPress>().enabled = false;
            }
            
            // 敵が全員出てくるまで待つ
            await UniTask.Delay(5000, cancellationToken: cancellationToken);
            
            PauseInGame();
            
            await FadeInGrayOut(cancellationToken);

            await ShowTutorialText(cancellationToken, "敵がたくさんいる時は\nピンチになりやすい！", 0f);
            await ShowTutorialText(cancellationToken, "「JUMBLE RUSH」で\nピンチを脱出しよう！", 0f);
            // ジャンブルラッシュゲージのハイライト
            HighlightTarget("rush_gauge");
            await ShowTutorialText(cancellationToken, "「JUMBLE RUSH」は\n「RUSHゲージ」がたまると使えるよ！", 0f);
            await ShowTutorialText(cancellationToken, "今回はゲージをMAXにするよ！", 0f);
            await HideTutorialText(cancellationToken);
            // ジャンブルラッシュゲージマックス処理
            SetFullRushGauge();
            
            await UniTask.Delay(700, cancellationToken: cancellationToken);
            
            await ShowTutorialText(cancellationToken, "それから、「キャラ」も\nいないとダメなんだ", 0f);
            await ShowTutorialText(cancellationToken, "今回はキャラの召喚に\n必要なリーダーPを0にするよ！", 0f);
            await HideTutorialText(cancellationToken);
            // コストゼロ対応
            SetSummonCostToZero();
            
            await ShowTutorialText(cancellationToken, "「キャラ」が多いほど\n「JUMBLE RUSH」は強くなるよ！", 0f);
            await ShowTutorialText(cancellationToken, "「キャラ」を全員召喚しよう！", 0f);
            await HideTutorialText(cancellationToken);
            UnHighlightTarget();

            // 編成済み数召喚する
            for (var i = 1; i <= partyCount; i++)
            {
                FindTargetObject("chara_button_" + i).GetComponent<Button>().enabled = true;
                ShowIndicator("chara_button_" + i);
                await WaitClickEvent(cancellationToken, "chara_button_" + i);
                HideIndicator();
                ResumeInGame();
                await UniTask.Delay(150, cancellationToken: cancellationToken);
                PauseInGame();
            }

            await ShowTutorialText(cancellationToken, "「JUMBLE RUSH」を使おう！", 0f);
            await HideTutorialText(cancellationToken);
            
            // ダイアログ表示タイミング調整のためジャンブルラッシュをスキップさせない
            DisableRushAnimSkip();
            
            rushGaugeButton.enabled = true;
            ShowIndicator("rush_gauge");
            await WaitClickEvent(cancellationToken, "rush_gauge");
            HideIndicator();
            await FadeOutGrayOut(cancellationToken);
            ResumeInGame();

            await UniTask.Delay(7500, cancellationToken:cancellationToken);
            
            PauseInGame();
            
            // ジャンブルラッシュについて1
            ShowTutorialDialogWithNextButton(tipModels[0].Title, tipModels[0].AssetPath);
            await WaitDismissModal<TutorialTipDialogViewController>(cancellationToken);

            // ジャンブルラッシュについて2
            ShowTutorialDialog(tipModels[1].Title, tipModels[1].AssetPath);
            await WaitDismissModal<TutorialTipDialogViewController>(cancellationToken);
            
            // 再開
            ResumeInGame();
            
            // InGamePresenterでチュートリアルのインゲームが完了しているか確認
            await UniTask.WaitUntil(IsEndTutorial, cancellationToken: cancellationToken);
                
            await ShowTutorialText(cancellationToken, "ありがとう！\n「ファントム」の侵攻を防げたよ！", 0);
            await ShowTutorialText(cancellationToken, "でも、まだ他の作品の世界も\n危ないらしいんだ！", 0);
            await ShowTutorialText(cancellationToken, "また、ファントムは、\n侵略した作品の世界のキャラをコピーして", 0);
            await ShowTutorialText(cancellationToken, "新たなファントムとして\n生み出すこともできるらしい...", 0);
            await ShowTutorialText(cancellationToken, "新たに生み出されたファントムは\nコピー元になったキャラと\n同じ力を使えるので注意しよう！！", 0);
            // \u00d7は×の表示
            await ShowTutorialText(cancellationToken, "まずは「SPY\u00d7FAMILY」の\n作品の世界を助けに行こう！\n", 0);
            await HideTutorialText(cancellationToken);
            // ホームへ遷移
            TransitionToHome();
        }
    }
}
