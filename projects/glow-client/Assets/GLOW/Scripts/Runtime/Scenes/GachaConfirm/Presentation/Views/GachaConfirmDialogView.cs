using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaConfirm.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.GachaConfirm.Presentation.Views
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-19_ガシャ確認ダイアログ
    /// </summary>
    public class GachaConfirmDialogView : UIView
    {
        [SerializeField] UIText _gachaAdDrawText;
        [SerializeField] UIText _gachaAdSkipDrawText;
        [SerializeField] UIText _gachaAdDrawEndText;
        [SerializeField] UIText _adGachaDrawableCountText;
        [SerializeField] UIText _adSkipDrawableCountText;
        [SerializeField] UIText _gachaTicketDrawText;
        [SerializeField] UIText _gachaDiamondDrawText;
        [SerializeField] UIText _adResetTimeText;
        [SerializeField] UIText _freeDiamondPriorityText;
        [SerializeField] UIText _diamondShortageText;
        [SerializeField] AmountSelectionComponent _amountSelectionComponent;
        [SerializeField] GameObject _ticketResourceArea;
        [SerializeField] GameObject _freeDiamondResourceArea;
        [SerializeField] GameObject _paidDiamondResourceArea;
        [SerializeField] GameObject _resourceArea;
        [Header("チケット消費")]
        [SerializeField] UIText _ticketAmountText;
        [SerializeField] UIText _ticketConsumptionAmountText;
        [Header("無償石消費")]
        [SerializeField] UIText _freeDiamondAmountText;
        [SerializeField] UIText _freeDiamondAfterConsumptionAmountText;
        [Header("有償石消費")]
        [SerializeField] UIText _paidDiamondAmountText;
        [SerializeField] UIText _paidDiamondAfterConsumptionAmountText;
        [Header("ボタン")]
        [SerializeField] UITextButton _cancelButton;
        [SerializeField] UITextButton _closeButton;
        [SerializeField] UITextButton _adDrawButton;
        [SerializeField] UITextButton _adSkipDrawButton;
        [SerializeField] UITextButton _drawButton;
        [SerializeField] Button _tutorialDrawButton;
        [SerializeField] UITextButton _shopButton;
        [SerializeField] UIImage _itemIconImage;

        ItemAmount _playerItemAmount = ItemAmount.Empty;
        ItemAmount _singleDrawConsumeAmount = ItemAmount.Empty;
        GachaDrawCount _gachaDrawCount = GachaDrawCount.Zero;
        GachaName _title = GachaName.Empty;
        GachaType _gachaType;

        public GachaDrawCount GachaDrawCount => new (_gachaDrawCount.Value);

        void RefreshView()
        {
            // ボタン
            _adDrawButton.gameObject.SetActive(false);
            _adSkipDrawButton.gameObject.SetActive(false);
            _drawButton.gameObject.SetActive(false);
            _cancelButton.gameObject.SetActive(false);
            _closeButton.gameObject.SetActive(false);
            _shopButton.gameObject.SetActive(false);

            // 広告
            _gachaAdDrawText.Hidden = true;
            _gachaAdDrawEndText.Hidden = true;
            _adResetTimeText.Hidden = true;

            // 広告スキップ
            _gachaAdSkipDrawText.Hidden = true;

            // ダイア
            _gachaDiamondDrawText.Hidden = true;
            _freeDiamondPriorityText.Hidden = true;
            _diamondShortageText.Hidden = true;
            _freeDiamondResourceArea.SetActive(false);
            _paidDiamondResourceArea.SetActive(false);

            // アイテム
            _gachaTicketDrawText.Hidden = true;
            _amountSelectionComponent.Hidden = true;
            _ticketResourceArea.SetActive(false);
        }

        public void SetViewModel(GachaConfirmDialogViewModel viewModel)
        {
            RefreshView();

            _title = viewModel.GachaName;
            // リソースタイプごとに表示を切り替える
            switch (viewModel.CostType)
            {
                case CostType.Ad:
                    _resourceArea.SetActive(false); // 広告ガシャのためリソースのエリアを非表示にする
                    _gachaAdDrawEndText.Hidden = viewModel.DrawableFlag.Value;   // ひけない場合表示
                    _adResetTimeText.Hidden = false;      // 広告のリセット時間表示

                    var text = TimeSpanFormatter.FormatUntilResetAd(viewModel.AdGachaResetRemainingTimeSpan);
                    _adResetTimeText.SetText(text);

                    _drawButton.gameObject.SetActive(false);                        // 広告ガチャの場合必ず非表示

                    // 回数上限の場合は閉じるボタンのみ表示する
                    _cancelButton.gameObject.SetActive(viewModel.DrawableFlag.Value);
                    _closeButton.gameObject.SetActive(!viewModel.DrawableFlag.Value);

                    if (viewModel.HeldAdSkipPassInfoViewModel.IsEmpty())
                    {
                        // パス未所持
                        _adDrawButton.gameObject.SetActive(viewModel.DrawableFlag.Value);
                        _adSkipDrawButton.gameObject.SetActive(false);
                        _gachaAdDrawText.Hidden = !viewModel.DrawableFlag.Value;     // ひける場合表示
                        _gachaAdDrawText.SetText("動画広告を視聴して\n{0}を{1}回引きますか？", _title.Value, viewModel.GachaDrawCount.Value);
                        _gachaAdSkipDrawText.Hidden = true;
                        _adGachaDrawableCountText.SetText(viewModel.AdGachaDrawableCount.ToRemainingCountString());
                    }
                    else
                    {
                        // パス所持
                        _adDrawButton.gameObject.SetActive(false);
                        _adSkipDrawButton.gameObject.SetActive(viewModel.DrawableFlag.Value);
                        _gachaAdDrawText.Hidden = true;
                        _gachaAdSkipDrawText.Hidden = !viewModel.DrawableFlag.Value;     // ひける場合表示
                        _gachaAdSkipDrawText.SetText(
                            "{0}を{1}回引きますか？\n({2}適用中)",
                            _title.Value,
                            viewModel.GachaDrawCount.Value,
                            viewModel.HeldAdSkipPassInfoViewModel.PassProductName.ToString());
                        _adSkipDrawableCountText.SetText(viewModel.AdGachaDrawableCount.ToRemainingCountString());
                    }

                    break;
                case CostType.Diamond:
                    _gachaDiamondDrawText.Hidden = false;
                    _freeDiamondPriorityText.Hidden = false; // 無償ダイア優先消費表示は必ず表示する
                    _diamondShortageText.Hidden = viewModel.DrawableFlag.Value;      // ひけない場合 不足表示
                    _freeDiamondResourceArea.SetActive(true);
                    _paidDiamondResourceArea.SetActive(true);
                    _freeDiamondAmountText.SetText(viewModel.PlayerFreeDiamondAmount.ToStringSeparated());
                    _freeDiamondAfterConsumptionAmountText.SetText(viewModel.PlayerFreeDiamondAmountAfterConsumption.ToStringSeparated());
                    _paidDiamondAmountText.SetText(viewModel.PlayerPaidDiamondAmount.ToStringSeparated());
                    _paidDiamondAfterConsumptionAmountText.SetText(viewModel.PlayerPaidDiamondAmountAfterConsumption.ToStringSeparated());
                    _gachaDiamondDrawText.SetText("プリズムを<color=#EE3628>{0}</color>個使用して\n{1}を{2}回引きますか？", viewModel.CostAmount.ToString(), _title.Value, viewModel.GachaDrawCount.Value);

                    // 回数上限の場合は閉じるボタンのみ表示する

                    // アイテム不足時はショップ遷移ボタン
                    _cancelButton.gameObject.SetActive(true);
                    _drawButton.gameObject.SetActive(viewModel.DrawableFlag.Value);
                    _shopButton.gameObject.SetActive(!viewModel.DrawableFlag.Value);
                    break;

                case CostType.PaidDiamond:
                    _gachaDiamondDrawText.Hidden = false;
                    _diamondShortageText.Hidden = viewModel.DrawableFlag.Value;  // ひけない場合 不足表示
                    _freeDiamondResourceArea.SetActive(true);
                    _paidDiamondResourceArea.SetActive(true);
                    _freeDiamondAmountText.SetText(viewModel.PlayerFreeDiamondAmount.ToStringSeparated());
                    _freeDiamondAfterConsumptionAmountText.SetText(viewModel.PlayerFreeDiamondAmountAfterConsumption.ToStringSeparated());
                    _paidDiamondAmountText.SetText(viewModel.PlayerPaidDiamondAmount.ToStringSeparated());
                    _paidDiamondAfterConsumptionAmountText.SetText(viewModel.PlayerPaidDiamondAmountAfterConsumption.ToStringSeparated());
                    _gachaDiamondDrawText.SetText("有償プリズムを<color=#EE3628>{0}</color>個使用して\n{1}を{2}回引きますか？", viewModel.CostAmount.ToString(), _title.Value, viewModel.GachaDrawCount.Value);

                    // 回数上限の場合は閉じるボタンのみ表示する

                    // アイテム不足時はショップ遷移ボタン
                    _cancelButton.gameObject.SetActive(true);
                    _drawButton.gameObject.SetActive(viewModel.DrawableFlag.Value);
                    _shopButton.gameObject.SetActive(!viewModel.DrawableFlag.Value);
                    break;

                case CostType.Item: // 消費アイテム不足時は「ひく」ボタングレイアウトでここまで遷移しない
                    _amountSelectionComponent.Hidden = false;
                    _ticketAmountText.SetText(viewModel.PlayerItemAmount.ToStringSeparated());
                    _cancelButton.gameObject.SetActive(true);
                    _drawButton.gameObject.SetActive(viewModel.DrawableFlag.Value);
                    UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_itemIconImage.Image, viewModel.PlayerResourceIconAssetPath.Value);
                    var afterAmount = new ItemAmount((viewModel.PlayerItemAmount.Value - (int)viewModel.CostAmount.Value));
                    _ticketConsumptionAmountText.SetText("{0}", afterAmount.ToStringSeparated());
                    _ticketResourceArea.SetActive(true);
                    _playerItemAmount = viewModel.PlayerItemAmount;
                    _gachaTicketDrawText.Hidden = false;
                    var costItemAmount = new ItemAmount((int)viewModel.CostAmount.Value);

                    _gachaType = viewModel.GachaType;
                    _gachaDrawCount = viewModel.GachaDrawCount;
                    _amountSelectionComponent.Hidden = !viewModel.GachaDrawCount.IsSingleDraw();

                    if (viewModel.GachaDrawCount.IsSingleDraw())
                    {
                        // 所持数で引ける回数(切り捨て)
                        var maxDrawCountByHasAmount = (int)(viewModel.PlayerItemAmount.Value / viewModel.CostAmount.Value);
                        // 引ける最大数の設定 最大数10or所持数から引ける最大数の小さい方
                        var maxAmountValue = GachaDrawCount.MaxGachaDrawCount < maxDrawCountByHasAmount
                            ? (int)GachaDrawCount.MaxGachaDrawCount.Value
                            : maxDrawCountByHasAmount;
                        var maxAmount = new ItemAmount(maxAmountValue);

                        _amountSelectionComponent.Setup(
                            ItemAmount.One,
                            maxAmount,
                            () => SetConsumeCountingText(viewModel.CostName));
                        _singleDrawConsumeAmount = costItemAmount;
                        SetConsumeCountingText(viewModel.CostName);
                    }
                    else
                    {
                        // 複数回ガシャ
                        SetMultiDrawConsumeCountingText(costItemAmount, viewModel.CostName);
                    }
                    
                    break;

                default:
                    break;
            }
        }

        void SetConsumeCountingText(ItemName costName)
        {
            // 使用チケット枚数表示
            _gachaDrawCount = new GachaDrawCount(_amountSelectionComponent.Amount.Value);
            var consumeAmount = new ItemAmount(_gachaDrawCount.Value * _singleDrawConsumeAmount.Value);

            _gachaTicketDrawText.SetText(
                "{0}を<color=#EE3628>{1}</color>枚使用して\n{2}を{3}回引きますか？",
                GetConsumeItemName(costName),
                consumeAmount.ToStringSeparated(),
                _title.Value,
                _gachaDrawCount.Value);
            _ticketConsumptionAmountText.SetText("{0}", (_playerItemAmount - consumeAmount).ToStringSeparated());
        }

        void SetMultiDrawConsumeCountingText(ItemAmount consumeAmount, ItemName costName)
        {
            _gachaTicketDrawText.SetText(
                "{0}を<color=#EE3628>{1}</color>枚使用して\n{2}を引きますか？",
                GetConsumeItemName(costName),
                consumeAmount.ToStringSeparated(),
                _title.Value);
        }

        string GetConsumeItemName(ItemName costName)
        {
            return _gachaType switch
            {
                // 消費アイテムでテキスト表示の切り替え
                GachaType.Medal => costName.Value,
                _ => "チケット"
            };
        }
    }
}
