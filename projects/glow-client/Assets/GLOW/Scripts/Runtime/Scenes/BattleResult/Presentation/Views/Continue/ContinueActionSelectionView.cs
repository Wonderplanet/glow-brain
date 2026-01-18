using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    /// <summary>
    /// 53_バトルリザルト
    /// 　53-2_敗北リザルト
    /// 　　53-2-1-2_コンティニュー
    /// 　　　53-2-1-2-1_コンティニュー確認ダイアログ
    /// </summary>
    public class ContinueActionSelectionView : UIView
    {
        [SerializeField] UIObject _remainingContinueObject;
        [SerializeField] UIText _remainingAdCountText;
        [SerializeField] UIText _useDiamondText;
        [SerializeField] ContinueEnemyProgressComponent _enemyProgressComponent;

        [Header("コンティニュー実行ボタン(広告)")]
        [SerializeField] UIObject _continueAdButtonGrayObject;
        [SerializeField] UIObject _continueAdButtonObject;
        [SerializeField] UITextButton _continueAdButton;

        [Header("コンティニュー実行ボタン(広告スキップ)")]
        [SerializeField] UIObject _continueAdSkipButtonGrayObject;
        [SerializeField] UIObject _continueAdSkipButtonObject;
        [SerializeField] UIText _heldAdSkipPassNameText;
        [SerializeField] UITextButton _continueAdSkipButton;

        public void SetUp(ContinueActionSelectionViewModel viewModel)
        {
            _enemyProgressComponent.SetUp(
                viewModel.RemainingTargetEnemyCount,
                viewModel.DefeatedBossCount,
                viewModel.TotalBossCount);

            bool canContinueAd = viewModel.RemainingContinueAdCount > ContinueCount.Zero;
            if (viewModel.HeldAdSkipPassInfoViewModel.IsEmpty())
            {
                // 広告スキップパスを所持していない場合
                _continueAdButtonObject.IsVisible = true;
                _continueAdButton.interactable = canContinueAd;
                _continueAdButtonGrayObject.IsVisible = !canContinueAd;
                _continueAdSkipButtonObject.IsVisible = false;
            }
            else
            {
                // 広告スキップパスを所持している場合
                _continueAdSkipButtonObject.IsVisible = true;
                _continueAdSkipButton.interactable = canContinueAd;
                _continueAdSkipButtonGrayObject.IsVisible = !canContinueAd;
                _heldAdSkipPassNameText.SetText(ZString.Format(
                    "{0}適用中",
                    viewModel.HeldAdSkipPassInfoViewModel.PassProductName.ToString()));
                _continueAdButtonObject.IsVisible = false;
            }

            if(viewModel.RemainingContinueAdCount <= ContinueCount.Zero)
            {
                _remainingContinueObject.IsVisible = false;
            }
            else
            {
                _remainingContinueObject.IsVisible = true;
                _remainingAdCountText.SetText("本日あと<color=#e82037>{0}回</color>", viewModel.RemainingContinueAdCount.Value);
            }
            _useDiamondText.SetText("×{0}", viewModel.Cost.ToStringSeparated());
        }
    }
}
