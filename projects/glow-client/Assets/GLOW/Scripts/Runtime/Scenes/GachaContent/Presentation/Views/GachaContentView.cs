using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaContent.Presentation.ViewModels;
using GLOW.Scenes.GachaList.Presentation.ViewModels.StepUpGacha;
using GLOW.Scenes.GachaList.Presentation.Views;
using GLOW.Scenes.GachaList.Presentation.Views.StepUpGacha;
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
        [Header("ガシャアセットバンドル配置エリア")]
        [SerializeField] CanvasGroup _gachaAssetAreaCanvasGroup;
        [SerializeField] RectTransform _gachaAssetArea;
        [SerializeField] GachaContentAssetTransition _gachaContentAssetTransition;

        [Header("ガシャ情報")]
        [Header("ガシャ情報/ヘッダー")]
        [SerializeField] GachaBandComponent _gachaBandComponent;
        [Header("ガシャ情報/残り時間")]
        [SerializeField] GameObject _gachaRemainingGameObject;
        [SerializeField] UIText _gachaRemainingText;
        [Header("ガシャ情報/確定回数など煽り文言")]
        [SerializeField] GameObject _gachaThresholdGameObject;
        [SerializeField] UIText _gachaThresholdText;

        [Header("ボタン情報")]
        [Header("ボタン情報/単体")]
        [SerializeField] Button _singleDrawButton;
        [SerializeField] UIText _gachaSingleCostText;
        [SerializeField] UIText _lackOfSingleItemText;
        [SerializeField] UIText _reachedLimitedCountTextSingle;
        [SerializeField] UIText _singleLimitedCountText;
        [SerializeField] UIText _singleDrawTicketCostText;
        [SerializeField] UIImage _gachaSingleCostImage;
        [SerializeField] UIImage _gachaSinglePaidCostImage;
        [SerializeField] GameObject _singleGachaButtonGrayOutGameObject;
        [SerializeField] GameObject _singleGachaButtonGameObject;
        [SerializeField] GameObject _singleGachaResources;    // 消費コスト表示部分
        [SerializeField] GameObject _singleDrawButtonInfoGameObject;    // "1回"部分
        [SerializeField] GameObject _singleFreeDrawGameObject;    // コスト無料表示
        [Header("ボタン情報/10連")]
        [SerializeField] Button _multiDrawButton;
        [SerializeField] UIText _multiDrawCountText;
        [SerializeField] UIText _gachaMultiCostText;
        [SerializeField] UIText _lackOfMultiItemText;
        [SerializeField] UIText _reachedLimitedCountTextMulti;
        [SerializeField] UIText _multiLimitedCountText;
        [SerializeField] UIText _multiDrawTicketCostText;
        [SerializeField] UIImage _gachaMultiCostImage;
        [SerializeField] UIImage _gachaMultiPaidCostImage;
        [SerializeField] GameObject _multiGachaButtonGrayOutGameObject;
        [SerializeField] GameObject _multiGachaButtonGameObject;
        [SerializeField] GameObject _multiGachaResources;     // 消費コスト表示部分
        [SerializeField] GameObject _multiDrawButtonInfoGameObject;     // "10回"部分
        [SerializeField] UIObject _fixedPrizeDescriptionObject; // 確定枠テキスト
        [SerializeField] UIText _fixedPrizeDescriptionText;
        [SerializeField] GameObject _multiFreeDrawGameObject;     // コスト無料表示
        [Header("ボタン情報/広告")]
        [SerializeField] Button _adDrawButton;
        [SerializeField] UIText _gachaAdButtonIntervalText;
        [SerializeField] UIText _adGachaDrawableCountText;
        [SerializeField] UIImage _gachaAdButtonGrayOutImage;
        [SerializeField] GameObject _adGachaButtonGameObject;
        [SerializeField] GameObject _adGachaButtonInfoGameObject;       // 広告ガチャボタン上のテキストとアイコン群
        [Header("ボタン情報/広告スキップ")]
        [SerializeField] Button _adSkipDrawButton;
        [SerializeField] UIText _gachaAdSkipButtonIntervalText;
        [SerializeField] UIText _gachaAdSkipPassNameText;
        [SerializeField] UIText _gachaAdSkipDrawableCountText;
        [SerializeField] UIImage _gachaAdSkipButtonGrayOutImage;
        [SerializeField] GameObject _adSkipGachaButtonGameObject;
        [SerializeField] GameObject _adSkipGachaButtonInfoGameObject;       // 広告スキップガチャボタン上のテキストとアイコン群
        [Header("ステップアップガシャ")]
        [SerializeField] StepUpGachaComponent _stepUpGachaComponent;
        [SerializeField] StepUpGachaUserStepCountComponent _stepUpGachaUserStepCountComponent;
        [Header("チュートリアル")]
        [SerializeField] TutorialGachaButtonComponent _tutorialGachaButtonComponent;


        GachaContentAssetComponent _instancedAsset; // 副作用起こすときだけここを呼び出す
        GachaContentAssetComponent InstancedAsset => _instancedAsset;

        public MasterDataId CurrentPickupMstUnitId
        {
            get
            {
                if (InstancedAsset == null)
                {
                    return MasterDataId.Empty;
                }

                return InstancedAsset.CurrentPickupMstUnitId;
            }
        }

        public void InitializeView()
        {
            _gachaAssetAreaCanvasGroup.alpha = 0;
            // NOTE: チュートリアルガシャは非表示にしておく。
            // 初回チュートリアルのとき、ここが呼ばれた後にTutorialSequenceからSetActiveされる想定
            _tutorialGachaButtonComponent.SetButtonEffectActive(false);
        }

        public void SetViewModel(
            GachaContentViewModel viewModel,
            StepUpGachaViewModel stepUpGachaViewModel,
            GachaContentAssetComponent gachaAssetObject,
            Action<bool> onAnimationStart)
        {
            UpdateInstancedAsset(gachaAssetObject, onAnimationStart);

            _gachaBandComponent.GachaContentBandSetup(viewModel.GachaType, viewModel.GachaName);

            SetGachaDescriptionAndRemainingTime(viewModel);
            SetSingleDrawButton(viewModel);
            SetMultiDrawButton(viewModel);
            SetGachaThreshold(viewModel);
            SetAdGachaButtons(viewModel);

            // ステップアップガシャの設定
            SetStepUpGachaComponents(stepUpGachaViewModel);

            _gachaAssetAreaCanvasGroup.alpha = 1f;
        }

        void UpdateInstancedAsset(GachaContentAssetComponent gachaAssetObject, Action<bool> onAnimationStart)
        {
            if (_instancedAsset != null)
            {
                Destroy(_instancedAsset.gameObject);
            }

            _instancedAsset = Instantiate(gachaAssetObject, _gachaAssetArea);
            _instancedAsset.InitializeView(onAnimationStart);
        }

        public void InitializeGachaContentAssetTransition(Action onTransitFill)
        {
            _gachaContentAssetTransition.InitializeView(onTransitFill);
        }

        public void StartGachaContentAssetTransitAnimation(float startTime)
        {
            _gachaContentAssetTransition.StartAnimation(startTime);
        }

        public void NextPickupAreaInformation()
        {
            if (InstancedAsset == null) return;

            InstancedAsset.NextPickupAreaInformation();
        }

        void SetGachaDescriptionAndRemainingTime(GachaContentViewModel viewModel)
        {
            var hasRemainingTime = !viewModel.GachaRemainingTimeText.IsEmpty();
            _gachaRemainingText.SetText(viewModel.GachaRemainingTimeText.Value);
            _gachaRemainingGameObject.SetActive(hasRemainingTime);
        }

        void SetSingleDrawButton(GachaContentViewModel viewModel)
        {
            _singleGachaButtonGameObject.SetActive(viewModel.SingleDrawButtonViewModel.IsDisplaySingleDrawButton);
            if(viewModel.ShouldShowSingleDrawButtonCostArea())
            {
                _gachaSingleCostText.SetText("×{0}", viewModel.SingleDrawButtonViewModel.SingleDrawCostAmount.ToString());
                if (viewModel.SingleDrawButtonViewModel.SingleDrawCostType == CostType.PaidDiamond)
                {
                    _gachaSingleCostImage.Hidden = true;
                    _gachaSinglePaidCostImage.Hidden = false;
                }
                else if (viewModel.SingleDrawButtonViewModel.SingleDrawCostType == CostType.Free)
                {
                    _gachaSingleCostImage.Hidden = true;
                    _gachaSinglePaidCostImage.Hidden = true;
                }
                else
                {
                    _gachaSingleCostImage.Hidden = false;
                    _gachaSinglePaidCostImage.Hidden = true;
                    UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                        _gachaSingleCostImage.Image,
                        viewModel.SingleDrawButtonViewModel.SingleDrawCostIconAssetPath.Value);
                }
            }
            _singleDrawButtonInfoGameObject.SetActive(viewModel.ShouldShowSingleDrawButtonInfo());

            _singleDrawTicketCostText.gameObject.SetActive(viewModel.ShouldShowSingleDrawTicketCostText());
            _singleDrawTicketCostText.SetText(viewModel.GetItemDrawCostText());

            _singleGachaButtonGrayOutGameObject.SetActive(viewModel.ShouldShowSingleDrawButtonGrayOut());

            _singleDrawButton.interactable = viewModel.IsSingleDrawButtonEnabled();

            _lackOfSingleItemText.gameObject.SetActive(viewModel.ShouldShowLackOfSingleItemText());
            _lackOfSingleItemText.SetText(viewModel.GetLackItemText());

            _reachedLimitedCountTextSingle.gameObject.SetActive(!viewModel.IsDrawableFlagByHasDrawLimitedCount);
            _singleGachaResources.SetActive(viewModel.ShouldShowSingleGachaResources());
            _singleFreeDrawGameObject.SetActive(viewModel.ShouldShowSingleGachaFreeText());
            _singleLimitedCountText.gameObject.SetActive(viewModel.ShouldShowSingleLimitedCountText());
            if(viewModel.SingleDrawButtonViewModel.SingleDrawLimitCount != GachaDrawLimitCount.Unlimited)
            {
                _singleLimitedCountText.SetText("1人{0}回まで利用可能", viewModel.SingleDrawButtonViewModel.SingleDrawLimitCount.Value);
            }
        }

        void SetMultiDrawButton(GachaContentViewModel viewModel)
        {
            _multiGachaButtonGameObject.SetActive(viewModel.MultiDrawButtonViewModel.IsDisplayMultiDrawButton);
            if (viewModel.ShouldShowMultiDrawButtonCostArea())
            {
                _gachaMultiCostText.SetText("×{0}", viewModel.MultiDrawButtonViewModel.MultiDrawCostAmount.ToString());
                if (viewModel.MultiDrawButtonViewModel.MultiDrawCostType == CostType.PaidDiamond)
                {
                    _gachaMultiCostImage.Hidden = true;
                    _gachaMultiPaidCostImage.Hidden = false;
                }
                else if (viewModel.MultiDrawButtonViewModel.MultiDrawCostType == CostType.Free)
                {
                    _gachaMultiCostImage.Hidden = true;
                    _gachaMultiPaidCostImage.Hidden = true;
                }
                else
                {
                    _gachaMultiCostImage.Hidden = false;
                    _gachaMultiPaidCostImage.Hidden = true;
                    UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_gachaMultiCostImage.Image,
                        viewModel.MultiDrawButtonViewModel.MultiDrawCostIconAssetPath.Value);
                }
            }
            _multiDrawCountText.SetText("{0}",viewModel.MultiDrawButtonViewModel.GachaDrawCount.Value);
            _fixedPrizeDescriptionObject.Hidden = viewModel.MultiDrawButtonViewModel.GachaFixedPrizeDescription.IsEmpty();
            _fixedPrizeDescriptionText.SetText(viewModel.MultiDrawButtonViewModel.GachaFixedPrizeDescription.Value);
            _multiDrawButtonInfoGameObject.SetActive(viewModel.ShouldShowMultiDrawButtonInfo());

            _multiDrawTicketCostText.gameObject.SetActive(viewModel.ShouldShowMultiDrawTicketCostText());
            _multiDrawTicketCostText.SetText(viewModel.GetItemDrawCostText());
            _multiGachaButtonGrayOutGameObject.SetActive(viewModel.ShouldShowMultiDrawButtonGrayOut());

            _lackOfMultiItemText.gameObject.SetActive(viewModel.ShouldShowLackOfMultiItemText());
            _lackOfMultiItemText.SetText(viewModel.GetLackItemText());

            _reachedLimitedCountTextMulti.gameObject.SetActive(!viewModel.IsDrawableFlagByHasDrawLimitedCount);
            _multiDrawButton.interactable = viewModel.IsMultiDrawButtonEnabled();
            _multiGachaResources.SetActive(viewModel.ShouldShowMultiGachaResources());
            _multiFreeDrawGameObject.SetActive(viewModel.ShouldShowMultiGachaFreeText());
            _multiLimitedCountText.gameObject.SetActive(viewModel.ShouldShowMultiLimitedCountText());
            if(viewModel.MultiDrawButtonViewModel.MultiDrawLimitCount != GachaDrawLimitCount.Unlimited)
            {
                _multiLimitedCountText.SetText("1人{0}回まで利用可能", viewModel.MultiDrawButtonViewModel.MultiDrawLimitCount.Value);
            }
        }

        void SetGachaThreshold(GachaContentViewModel viewModel)
        {
            var isEmpty = viewModel.GachaThresholdText.IsEmpty();
            _gachaThresholdText.Hidden = isEmpty;
            _gachaThresholdText.SetText(viewModel.GachaThresholdText.Value);
            _gachaThresholdGameObject.SetActive(!isEmpty);
        }

        void SetAdGachaButtons(GachaContentViewModel viewModel)
        {
            if (!viewModel.AdDrawButtonViewModel.IsDisplayAdGachaDrawButton)
            {
                _adSkipGachaButtonGameObject.SetActive(false);
                _adGachaButtonGameObject.SetActive(false);
                return;
            }

            if(viewModel.AdDrawButtonViewModel.HeldAdSkipPassInfoViewModel.IsEmpty())
            {
                // 広告ガチャボタン設定(パスによる広告スキップがない場合)
                _adGachaButtonGameObject.SetActive(true);
                _adSkipGachaButtonGameObject.SetActive(false);

                SetUpAdGachaDrawButton(
                    viewModel.AdDrawButtonViewModel.CanAdGachaDraw,
                    viewModel.AdDrawButtonViewModel.AdGachaResetRemainingText,
                    viewModel.AdDrawButtonViewModel.AdGachaDrawableCount);
            }
            else
            {
                // 広告スキップガチャボタン設定(パスによる広告スキップがある場合)
                _adGachaButtonGameObject.SetActive(false);
                _adSkipGachaButtonGameObject.SetActive(true);

                SetUpAdSkipGachaDrawButton(
                    viewModel.AdDrawButtonViewModel.CanAdGachaDraw,
                    viewModel.AdDrawButtonViewModel.HeldAdSkipPassInfoViewModel.PassProductName,
                    viewModel.AdDrawButtonViewModel.AdGachaResetRemainingText,
                    viewModel.AdDrawButtonViewModel.AdGachaDrawableCount);
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

        void SetStepUpGachaComponents(StepUpGachaViewModel stepUpGachaViewModel)
        {
            if (stepUpGachaViewModel.IsEmpty())
            {
                _stepUpGachaComponent.Hidden = true;
                _stepUpGachaUserStepCountComponent.Hidden = true;
                return;
            }

            _stepUpGachaComponent.Hidden = false;
            _stepUpGachaUserStepCountComponent.Hidden = false;
            _stepUpGachaComponent.Setup(stepUpGachaViewModel);
            _stepUpGachaUserStepCountComponent.Setup(stepUpGachaViewModel.StepUpStepCount);
        }
    }
}
