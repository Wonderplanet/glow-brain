using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.ShopBuyConform.Presentation.Component;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    /// <summary>
    /// 53_バトルリザルト
    /// 　53-2_敗北リザルト
    /// 　　53-2-1-2_コンティニュー
    /// 　　　53-2-1-2-2_コンティニューダイアログ（プリズム）
    /// </summary>
    public class ContinueDiamondView : UIView
    {
        [SerializeField] ContinueEnemyProgressComponent _enemyProgressComponent;
        [SerializeField] UseResourceAmountChangeDisplayComponent _paidDiamondChangeComponent;
        [SerializeField] UseResourceAmountChangeDisplayComponent _freeDiamondChangeComponent;
        [SerializeField] UIText _confirmText;
        [Header("注意文")]
        [SerializeField] UIText _attentionShortageText;
        [Header("ボタン")]
        [SerializeField] UITextButton _continueButton;
        [SerializeField] UITextButton _buyButton;

        public void SetUp(ContinueDiamondViewModel viewModel)
        {
            _enemyProgressComponent.SetUp(
                viewModel.RemainingTargetEnemyCount,
                viewModel.DefeatedBossCount,
                viewModel.TotalBossCount);

            _paidDiamondChangeComponent.SetupPaidDiamondAmount(
                viewModel.BeforePaidDiamond,
                viewModel.AfterPaidDiamond);

            _freeDiamondChangeComponent.SetupFreeDiamondAmount(
                viewModel.BeforeFreeDiamond,
                viewModel.AfterFreeDiamond);

            _confirmText.SetText("プリズムを{0}個使用して\nコンティニューしますか？", viewModel.Cost.ToStringSeparated());

            _attentionShortageText.gameObject.SetActive(viewModel.IsLackOfDiamond);

            _continueButton.gameObject.SetActive(!viewModel.IsLackOfDiamond);
            _buyButton.gameObject.SetActive(viewModel.IsLackOfDiamond);
        }
    }
}
