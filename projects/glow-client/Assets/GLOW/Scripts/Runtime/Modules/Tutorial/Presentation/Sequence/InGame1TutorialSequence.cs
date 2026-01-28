using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.TutorialTipDialog.Presentation.View;
using UnityEngine.UI;
using WPFramework.Presentation.Components;

namespace GLOW.Modules.Tutorial.Presentation.Sequence
{
    /// <summary>
    /// メインパートチュートリアル
    /// チュートリアルステージ2 チュートリアルシーケンス
    /// </summary>
    public class InGame1TutorialSequence : BaseInGameTutorialSequence
    {
        public override async UniTask Play(CancellationToken cancellationToken)
        {
            var tipModels = GetTutorialTips(new MasterDataId("InGame1"));

            // ユニットのボタンを無効化
            SetCharaButtonActiveState(false);
            
            // チュートリアルシーケンス開始
            PauseInGame();
            await FadeInGrayOut(cancellationToken);
            
            HighlightTarget("koma_set_5_2"); // Koma_set_5のComponent_2プレハブ
            await ShowTutorialText(cancellationToken, "このステージには特殊なコマがあるよ！", 0f);
            await ShowTutorialText(cancellationToken, "特殊なコマには、「キャラ」に\n有利や不利に働く効果があるんだ", 0f);
            await ShowTutorialText(cancellationToken, "不利な効果のコマに対しては、\n「キャラ」の特性で対抗しよう！", 0f);
            await HideTutorialText(cancellationToken);
            UnHighlightTarget();
            
            // ダイアログ表示 特殊コマについて1
            ShowTutorialDialogWithNextButton(tipModels[0].Title, tipModels[0].AssetPath);
            await WaitDismissModal<TutorialTipDialogViewController>(cancellationToken);

            // ダイアログ表示 特殊コマについて2
            ShowTutorialDialog(tipModels[1].Title, tipModels[1].AssetPath);
            await WaitDismissModal<TutorialTipDialogViewController>(cancellationToken);
            
            await FadeOutGrayOut(cancellationToken);
            ResumeInGame();
            
            // 敵出現まで待機
            await UniTask.Delay(3100, cancellationToken: cancellationToken);
            
            PauseInGame();
            await FadeInGrayOut(cancellationToken);
            
            await ShowTutorialText(cancellationToken, "「ファントム」が出てきた！", 0f);
            await ShowTutorialText(cancellationToken, "今なら「パワーアップコマ」の中で\n戦えそうだよ！", 0f);
            await ShowTutorialText(cancellationToken, "1回だけリーダーPを使わずに\n「キャラ」を召喚できるようにするよ", 0f);
            await ShowTutorialText(cancellationToken, "キャラを召喚して\nパワーアップコマの中で戦ってみよう！", 0f);
            await HideTutorialText(cancellationToken);
            
            // 1体目のコストを0にする
            SetOneUnitSummonCostToZero();
            
            // 1体目のキャラを選択
            var button1 = FindTargetObject("chara_button_1").GetComponent<Button>();
            button1.enabled = true;
            ShowIndicator("chara_button_1");
            await WaitClickEvent(cancellationToken, "chara_button_1");
            HideIndicator();
            
            // ユニットのボタンを有効化
            SetCharaButtonActiveState(true);
            
            // 終了
            await FadeOutGrayOut(cancellationToken);
            ResumeInGame();
        }

        void SetCharaButtonActiveState(bool enabled)
        {
            // ユニットのボタン名
            var buttonNames = new[] { "chara_button_1", "chara_button_2", "chara_button_3", "chara_button_4", "chara_button_5" };
            
            // ユニットアイコンのボタンを無効化
            foreach (var buttonName in buttonNames)
            {
                var button = FindTargetObject(buttonName).GetComponent<Button>();
                button.enabled = enabled;
            }
            
            // ユニットアイコンのボタンの長押しを無効化
            foreach (var buttonName in buttonNames)
            {
                var longPressButton = FindTargetObject(buttonName).GetComponent<UIButtonLongPress>();
                longPressButton.enabled = enabled;
            }
        }
    }
}
