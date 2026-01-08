using  System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaContent.Presentation.ViewModels;
using GLOW.Scenes.GachaList.Presentation.Views;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.GachaContent.Presentation.Views
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-1_ガシャトップ
    /// </summary>
    public class GachaContentView : UIView
    {
        [Header("ガシャ情報")]
        [SerializeField] GachaBandComponent _gachaBandComponent;
        [SerializeField] UIText _gachaDescriptionNoLimitText;
        [SerializeField] UIText _gachaDescriptionText;
        [SerializeField] GameObject _backgroundFesParent;
        [SerializeField] GameObject _backgroundPickupParent;
        [SerializeField] GameObject _backgroundAdParent;
        [SerializeField] GameObject _backgroundTicketParent;
        [SerializeField] UIText _gachaRemainingText;
        [SerializeField] UIText _gachaThresholdText;
        [SerializeField] SeriesLogoComponent _gachaUnitSeriesLogo;
        [SerializeField] GameObject _gachaRemainingGameObject;
        [SerializeField] GameObject _gachaThresholdGameObject;
        [SerializeField] GameObject _limitedIconGameObject;
        [SerializeField] UIImage _gachaLogoImage;
        
        [Header("ユニット情報")]
        [SerializeField] GachaUnitAvatarPageListComponent _avatarPageListComponent;
        [SerializeField] GachaUnitNameBarComponent _unitNameBarComponent;
        [SerializeField] GachaContentCutInAnimationComponent _gachaContentCutInAnimationComponent;
        [SerializeField] UIText _unitIncitingText;
        [SerializeField] Button _unitDetailButton;
        [SerializeField] GameObject _unitChangeArrow;
        [Header("ボタン情報")]
        [SerializeField] Button _adDrawButton;
        [SerializeField] Button _singleDrawButton;
        [SerializeField] Button _multiDrawButton;
        [SerializeField] UIText _gachaAdButtonIntervalText;
        [SerializeField] UIText _adGachaDrawableCountText;
        [SerializeField] UIText _gachaSingleCostText;
        [SerializeField] UIText _gachaMultiCostText;
        [SerializeField] UIText _lackOfSingleItemText;
        [SerializeField] UIText _lackOfMultiItemText;
        [SerializeField] UIText _reachedLimitedCountTextSingle;
        [SerializeField] UIText _reachedLimitedCountTextMulti;
        [SerializeField] UIText _singleLimitedCountText;
        [SerializeField] UIText _multiLimitedCountText;
        [SerializeField] UIText _singleDrawTicketCostText;
        [SerializeField] UIText _multiDrawTicketCostText;
        [SerializeField] UIImage _gachaAdButtonGrayOutImage;
        [SerializeField] UIImage _gachaSingleCostImage;
        [SerializeField] UIImage _gachaSinglePaidCostImage;
        [SerializeField] UIImage _gachaMultiCostImage;
        [SerializeField] UIImage _gachaMultiPaidCostImage;
        [SerializeField] GameObject _adGachaButtonGameObject;
        [SerializeField] GameObject _singleGachaButtonGrayOutGameObject;
        [SerializeField] GameObject _multiGachaButtonGrayOutGameObject;
        [SerializeField] GameObject _singleGachaButtonGameObject;
        [SerializeField] GameObject _multiGachaButtonGameObject;
        [SerializeField] GameObject _adGachaButtonInfoGameObject;       // 広告ガチャボタン上のテキストとアイコン群
        [SerializeField] GameObject _singleGachaResources;    // 消費コスト表示部分
        [SerializeField] GameObject _multiGachaResources;     // 消費コスト表示部分
        [SerializeField] GameObject _singleDrawButtonInfoGameObject;    // "1回"部分
        [SerializeField] GameObject _multiDrawButtonInfoGameObject;     // "10回"部分
        [SerializeField] UIObject _fixedPrizeDescriptionObject; // 確定枠テキスト
        [SerializeField] UIText _fixedPrizeDescriptionText;

        [Header("広告スキップボタン情報")]
        [SerializeField] Button _adSkipDrawButton;
        [SerializeField] UIText _gachaAdSkipButtonIntervalText;
        [SerializeField] UIText _gachaAdSkipPassNameText;
        [SerializeField] UIText _gachaAdSkipDrawableCountText;
        [SerializeField] UIImage _gachaAdSkipButtonGrayOutImage;
        [SerializeField] GameObject _adSkipGachaButtonGameObject;
        [SerializeField] GameObject _adSkipGachaButtonInfoGameObject;       // 広告スキップガチャボタン上のテキストとアイコン群

        public GachaUnitAvatarPageListComponent AvatarPageList => _avatarPageListComponent;
        public bool IsTransitioning => _avatarPageListComponent.IsTransitioning;


        public void SetViewModel(GachaContentViewModel viewModel)
        {
            _gachaBandComponent.GachaContentBandSetup(viewModel.GachaType, viewModel.GachaName);

            SetGachaDescriptionAndRemainingTime(viewModel);
            SetSingleDrawButton(viewModel);
            SetMultiDrawButton(viewModel);
            SetGachaThreshold(viewModel);
            SetAdGachaButtons(viewModel);
            SetBackground(viewModel.GachaType);
            
        }

        void SetGachaDescriptionAndRemainingTime(GachaContentViewModel viewModel)
        {
            UISpriteUtil.LoadSpriteWithFade(_gachaLogoImage.Image, viewModel.GachaLogoAssetPath.Value);
            var isVisibleGachaLogo = !viewModel.GachaLogoAssetPath.IsEmpty();

            _gachaDescriptionNoLimitText.Hidden = isVisibleGachaLogo;
            _gachaDescriptionText.Hidden = !isVisibleGachaLogo;
            
            if (isVisibleGachaLogo)
            {
                _gachaDescriptionText.SetText(viewModel.GachaDescription.Value);
            }
            else
            {
                _gachaDescriptionNoLimitText.SetText(viewModel.GachaDescription.Value);
            }
                        
            var hasRemainingTime = !viewModel.GachaRemainingTimeText.IsEmpty();
            if (hasRemainingTime)
            {
                _gachaRemainingText.SetText(viewModel.GachaRemainingTimeText.Value);
            }
            else
            {
                _gachaDescriptionNoLimitText.SetText(viewModel.GachaDescription.Value);
                _gachaRemainingText.Hidden = true;
            }
            _gachaDescriptionText.SetText(viewModel.GachaDescription.Value);
            _gachaRemainingGameObject.SetActive(hasRemainingTime);
        }

        void SetSingleDrawButton(GachaContentViewModel viewModel)
        {
            _singleGachaButtonGameObject.SetActive(viewModel.IsDisplaySingleDrawButton);
            if(viewModel.ShouldShowSingleDrawButtonCostArea())
            {
                _gachaSingleCostText.SetText("×{0}", viewModel.SingleDrawCostAmount.ToString());
                if (viewModel.SingleDrawCostType == CostType.PaidDiamond)
                {
                    _gachaSingleCostImage.Hidden = true;
                    _gachaSinglePaidCostImage.Hidden = false;
                }
                else
                {
                    UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                        _gachaSingleCostImage.Image,
                        viewModel.SingleDrawCostIconAssetPath.Value);
                }
            }
            _singleDrawButtonInfoGameObject.SetActive(viewModel.ShouldShowSingleDrawButtonInfo());
            _singleDrawTicketCostText.gameObject.SetActive(viewModel.ShouldShowSingleDrawTicketCostText());
            _singleGachaButtonGrayOutGameObject.SetActive(viewModel.ShouldShowSingleDrawButtonGrayOut());
            _singleDrawButton.interactable = viewModel.IsSingleDrawButtonEnabled();
            _lackOfSingleItemText.gameObject.SetActive(viewModel.ShouldShowLackOfSingleItemText());
            _reachedLimitedCountTextSingle.gameObject.SetActive(!viewModel.IsDrawableFlagByHasDrawLimitedCount);
            _singleGachaResources.SetActive(viewModel.ShouldShowSingleGachaResources());
            _singleLimitedCountText.gameObject.SetActive(viewModel.ShouldShowSingleLimitedCountText());
            if(viewModel.SingleDrawLimitCount != GachaDrawLimitCount.Unlimited)
            {
                _singleLimitedCountText.SetText("1人{0}回まで利用可能", viewModel.SingleDrawLimitCount.Value);
            }
        }

        void SetMultiDrawButton(GachaContentViewModel viewModel)
        {
            _multiGachaButtonGameObject.SetActive(viewModel.IsDisplayMultiDrawButton);
            if (viewModel.ShouldShowMultiDrawButtonCostArea())
            {
                _gachaMultiCostText.SetText("×{0}", viewModel.MultiDrawCostAmount.ToString());
                if (viewModel.MultiDrawCostType == CostType.PaidDiamond)
                {
                    _gachaMultiCostImage.Hidden = true;
                    _gachaMultiPaidCostImage.Hidden = false;
                }
                else
                {
                    UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_gachaMultiCostImage.Image,
                        viewModel.MultiDrawCostIconAssetPath.Value);
                }
            }
            _fixedPrizeDescriptionObject.Hidden = viewModel.GachaFixedPrizeDescription.IsEmpty();
            _fixedPrizeDescriptionText.SetText(viewModel.GachaFixedPrizeDescription.Value);
            _multiDrawButtonInfoGameObject.SetActive(viewModel.ShouldShowMultiDrawButtonInfo());
            _multiDrawTicketCostText.gameObject.SetActive(viewModel.ShouldShowMultiDrawTicketCostText());
            _multiGachaButtonGrayOutGameObject.SetActive(viewModel.ShouldShowMultiDrawButtonGrayOut());
            _lackOfMultiItemText.gameObject.SetActive(viewModel.ShouldShowLackOfMultiItemText());
            _reachedLimitedCountTextMulti.gameObject.SetActive(!viewModel.IsDrawableFlagByHasDrawLimitedCount);
            _multiDrawButton.interactable = viewModel.IsMultiDrawButtonEnabled();
            _multiGachaResources.SetActive(viewModel.ShouldShowMultiGachaResources());
            _multiLimitedCountText.gameObject.SetActive(viewModel.ShouldShowMultiLimitedCountText());
            if(viewModel.MultiDrawLimitCount != GachaDrawLimitCount.Unlimited)
            {
                _multiLimitedCountText.SetText("1人{0}回まで利用可能", viewModel.MultiDrawLimitCount.Value);
            }
        }

        void SetGachaThreshold(GachaContentViewModel viewModel)
        {
            var isEmpty = viewModel.GachaThresholdText.IsEmpty();
            _gachaThresholdText.Hidden = isEmpty;
            _gachaThresholdText.SetText(viewModel.GachaThresholdText.Value);
            _gachaThresholdGameObject.SetActive(!isEmpty);
        }

        void SetBackground(GachaType gachaType)
        {
            _backgroundFesParent.SetActive(false);
            _backgroundPickupParent.SetActive(false);
            _backgroundAdParent.SetActive(false);
            _backgroundTicketParent.SetActive(false);

            switch (gachaType)
            {
                case GachaType.PaidOnly:
                case GachaType.Festival:
                    _backgroundFesParent.SetActive(true);
                    break;
                case GachaType.Tutorial:
                case GachaType.Pickup:
                    _backgroundPickupParent.SetActive(true);
                    break;
                case GachaType.Free:
                    _backgroundAdParent.SetActive(true);
                    break;
                case GachaType.Ticket:
                    _backgroundTicketParent.SetActive(true);
                    break;
            }
        }

        public void SetUnitInfo(GachaDisplayUnitViewModel model, Action action)
        {
            // アクション設定
            _gachaContentCutInAnimationComponent.SetEndAnimationAction(action);
            // 表示キャラ切り替え
            UpdateUnitInfo(model);
        }

        public void ChangeUnitInfo(GachaDisplayUnitViewModel model)
        {
            UpdateUnitInfo(model);
        }

        public void ReplayUnitCutInAnimation()
        {
            _gachaContentCutInAnimationComponent.ReplayUnitCutInAnimation();
        }

        public bool IsUnitCutInAnimationPlaying()
        {
            return _gachaContentCutInAnimationComponent.IsAnimationPlaying();
        }

        public void HideUnitInfo()
        {
            _unitNameBarComponent.Hidden = true;
            _gachaUnitSeriesLogo.gameObject.SetActive(false);
            _gachaContentCutInAnimationComponent.Hidden = true;
            _unitDetailButton.gameObject.SetActive(false);
            _unitChangeArrow.SetActive(false);
        }

        void UpdateUnitInfo(GachaDisplayUnitViewModel model)
        {
            _unitNameBarComponent.Setup(model.Rarity, model.Name, model.RoleType, model.CharacterColor);
            _gachaUnitSeriesLogo.Setup(model.SeriesLogoImagePath);

            // カットイン背景の更新
            _gachaContentCutInAnimationComponent.UpdateUnitInfo(model.ContentCutInAssetPath, model.Rarity);

            // 煽り文言表示
            _unitIncitingText.SetText(model.DisplayUnitDescription.Value);
        }

        void SetAdGachaButtons(GachaContentViewModel viewModel)
        {
            if (!viewModel.IsDisplayGachaAdDrawButton)
            {
                _adSkipGachaButtonGameObject.SetActive(false);
                _adGachaButtonGameObject.SetActive(false);
                return;
            }

            if(viewModel.HeldAdSkipPassInfoViewModel.IsEmpty())
            {
                // 広告ガチャボタン設定(パスによる広告スキップがない場合)
                _adGachaButtonGameObject.SetActive(true);
                _adSkipGachaButtonGameObject.SetActive(false);

                SetUpAdGachaDrawButton(
                    viewModel.CanAdGachaDraw,
                    viewModel.AdGachaResetRemainingText,
                    viewModel.AdGachaDrawableCount);
            }
            else
            {
                // 広告スキップガチャボタン設定(パスによる広告スキップがある場合)
                _adGachaButtonGameObject.SetActive(false);
                _adSkipGachaButtonGameObject.SetActive(true);

                SetUpAdSkipGachaDrawButton(
                    viewModel.CanAdGachaDraw,
                    viewModel.HeldAdSkipPassInfoViewModel.PassProductName,
                    viewModel.AdGachaResetRemainingText,
                    viewModel.AdGachaDrawableCount);
            }
        }

        void SetUpAdGachaDrawButton(
            AdGachaDrawableFlag canAdGachaDraw,
            AdGachaResetRemainingText adGachaResetRemainingText,
            AdGachaDrawableCount adGachaDrawableCount)
        {
            _adDrawButton.interactable = canAdGachaDraw;
            _gachaAdButtonGrayOutImage.Hidden = canAdGachaDraw;
            _gachaAdButtonIntervalText.Hidden = canAdGachaDraw;
            _gachaAdButtonIntervalText.SetText(adGachaResetRemainingText.Value);
            _adGachaButtonInfoGameObject.SetActive(canAdGachaDraw);
            _adGachaDrawableCountText.SetText(adGachaDrawableCount.ToRemainingCountString());
        }

        void SetUpAdSkipGachaDrawButton(
            AdGachaDrawableFlag canAdGachaDraw,
            PassProductName passProductName,
            AdGachaResetRemainingText adGachaResetRemainingText,
            AdGachaDrawableCount adGachaDrawableCount)
        {
            _gachaAdSkipPassNameText.SetText("{0}適用中", passProductName.Value);
            _gachaAdSkipDrawableCountText.SetText(adGachaDrawableCount.ToRemainingCountString());

            _adSkipDrawButton.interactable = canAdGachaDraw;
            _gachaAdSkipButtonGrayOutImage.Hidden = canAdGachaDraw;
            _gachaAdSkipButtonIntervalText.Hidden = canAdGachaDraw;
            _gachaAdSkipButtonIntervalText.SetText(adGachaResetRemainingText.Value);
            _adSkipGachaButtonInfoGameObject.SetActive(canAdGachaDraw);
        }
    }
}
