using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.GachaList.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.GachaList.Presentation.Views
{
    public class MedalGachaComponent : UIObject
    {
        [SerializeField] RawImage _bannerRawImage;
        [SerializeField] UIText _gachaDescription;
        [SerializeField] UIText _costAmountText;
        [SerializeField] UIText _thresholdText;
        [SerializeField] UIText _remainingTimeText;
        [SerializeField] UIImage _gachaDrawButtonGrayOutImage;
        [SerializeField] UIImage _costIconImage;
        [SerializeField] Button _gachaInfoButton;
        [SerializeField] Button _gachaDrawButton;
        [SerializeField] GameObject _gachaButtonInfo;
        [SerializeField] GameObject _limitedIcon;
        [SerializeField] GameObject _limitlessIcon;

        public MasterDataId GachaId => _gachaId;
        MasterDataId _gachaId;
        Action<MasterDataId, GachaDrawType> _drawButtonTappedAction;
        Action<MasterDataId> _infoButtonAction;

        public void Setup(MedalGachaBannerViewModel viewModel, Action<MasterDataId, GachaDrawType> drawButtonTappedAction, Action<MasterDataId> infoAction)
        {
            _gachaId = viewModel.GachaId;
            UIBannerLoaderEx.Main.LoadBannerWithFadeIfNotLoaded(_bannerRawImage, viewModel.GachaBannerAssetPath.Value);
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_costIconImage.Image, viewModel.IconAssetPath.Value);
            _costAmountText.SetText("×{0}", viewModel.DrawCostAmount.ToString());
            _gachaDescription.SetText(viewModel.GachaDescription.Value);
            _thresholdText.SetText(viewModel.GachaThresholdText.Value);

            _limitlessIcon.SetActive(viewModel.GachaRemainingTimeText == GachaRemainingTimeText.Empty);
            _limitedIcon.SetActive(viewModel.GachaRemainingTimeText != GachaRemainingTimeText.Empty);

            if (viewModel.GachaRemainingTimeText != GachaRemainingTimeText.Empty)
            {
                _remainingTimeText.SetText(viewModel.GachaRemainingTimeText.Value);
            }

            // 消費コストが足りない場合グレーアウトして不足表示をする
            _gachaDrawButtonGrayOutImage.Hidden = !viewModel.DrawableFlag.IsTrue();
            _gachaButtonInfo.SetActive(viewModel.DrawableFlag.IsTrue());
            _gachaDrawButton.interactable = viewModel.DrawableFlag.IsTrue();

            // ボタンアクションの設定
            _drawButtonTappedAction = drawButtonTappedAction;
            _infoButtonAction = infoAction;

            // ボタンにアクションを登録
            _gachaDrawButton.onClick.AddListenerAsExclusive(() => _drawButtonTappedAction?.Invoke(_gachaId, GachaDrawType.Single));
            _gachaInfoButton.onClick.AddListenerAsExclusive(() => _infoButtonAction?.Invoke(_gachaId));
        }
    }
}
