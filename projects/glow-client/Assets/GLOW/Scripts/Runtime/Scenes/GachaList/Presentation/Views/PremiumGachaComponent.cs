using System;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.GachaList.Presentation.ViewModels;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.GachaList.Presentation.Views
{
    public class PremiumGachaComponent : UIObject
    {
        [SerializeField] GameObject _adGachaButtonInfoGameObject;
        [SerializeField] UIText _singleDrawCostAmountText;
        [SerializeField] UIText _multiDrawCostAmountText;
        [SerializeField] UIText _thresholdText;
        [SerializeField] UIText _gachaDescriptionText;
        [SerializeField] UIText _adGachaDrawableCountText;
        [SerializeField] UIImage _singleDrawCostIconImage;
        [SerializeField] UIImage _multiDrawCostIconImage;
        [SerializeField] UIImage _adGachaDrawButtonGrayOutImage;
        [SerializeField] RawImage _bannerRawImage;
        [SerializeField] UIText _adIntervalText;
        [SerializeField] Button _adGachaDrawButton;
        [SerializeField] Button _gachaSingleDrawButton;
        [SerializeField] Button _gachaMultiDrawButton;
        [SerializeField] Button _gachaRatioButton;
        [SerializeField] Button _gachaDetailButton;
        [SerializeField] UIImage _adNotificationBadgeIcon;
        [SerializeField] GameObject _drawCostGameObject;
        [SerializeField] GameObject _drawTicketCostGameObject;
        [SerializeField] UIObject _fixedPrizeDescriptionObject;
        [SerializeField] UIText _fixedPrizeDescriptionText;
        
        [Header("残り期間情報")]
        [SerializeField] UIText _remainingTime;
        [SerializeField] GameObject _remainingTextParent;
        [SerializeField] GameObject _limitlessIcon;
        
        [Header("広告スキップボタン")]
        [SerializeField] GameObject _adSkipGachaButtonInfoGameObject;
        [SerializeField] Button _adSkipGachaDrawButton;
        [SerializeField] UIText _adSkipPassNameText;
        [SerializeField] UIText _adSkipIntervalText;
        [SerializeField] UIText _adSkipDrawableCountText;
        [SerializeField] UIImage _adSkipGachaDrawButtonGrayOutImage;
        [SerializeField] UIImage _adSkipNotificationBadgeIcon;

        MasterDataId _gachaId;
        public MasterDataId GachaId => _gachaId;
        Action<MasterDataId, GachaDrawType> _drawButtonTappedAction;
        Action<MasterDataId> _gachaRatioButtonAction;
        Action<MasterDataId> _gachaDetailButtonAction;

        public void Setup(
            PremiumGachaViewModel model,
            HeldAdSkipPassInfoViewModel heldAdSkipPassInfoViewModel,
            Action<MasterDataId, GachaDrawType> drawButtonTappedAction,
            Action<MasterDataId> gachaRatioAction,
            Action<MasterDataId> gachaDetailAction)
        {
            _gachaId = new MasterDataId(model.GachaId.Value);

            UIBannerLoaderEx.Main.LoadBannerWithFadeIfNotLoaded(_bannerRawImage, model.GachaBannerAssetPath.Value);

            _gachaDescriptionText.SetText(model.GachaDescription.Value);
            _singleDrawCostAmountText.SetText("×{0}", model.SingleDrawCostAmount.ToString());
            _multiDrawCostAmountText.SetText("×{0}", model.MultiDrawCostAmount.ToString());
            _thresholdText.SetText(model.GachaThresholdText.Value);
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_singleDrawCostIconImage.Image, model.SingleDrawCostIconAssetPath.Value);
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(_multiDrawCostIconImage.Image, model.MultiDrawCostIconAssetPath.Value);

            if (heldAdSkipPassInfoViewModel.IsEmpty())
            {
                // 広告ガチャ設定
                SetUpAdGachaButton(model);
            }
            else
            {
                // 広告スキップガチャ設定
                SetUpAdSkipGachaButton(model, heldAdSkipPassInfoViewModel.PassProductName);
            }

            // ガチャボタンテキストの切り替え
            _drawCostGameObject.SetActive(model.CostType != CostType.Item);
            _drawTicketCostGameObject.SetActive(model.CostType == CostType.Item);

            // ガシャをひくボタンタップ時に確認ダイアログ表示するアクション
            _drawButtonTappedAction = drawButtonTappedAction;
            _gachaRatioButtonAction = gachaRatioAction;
            _gachaDetailButtonAction = gachaDetailAction;
                
            // ボタン設定
            _gachaId = model.GachaId;
            _adGachaDrawButton.onClick.AddListenerAsExclusive(OnGachaAdDrawButtonTapped);
            _adSkipGachaDrawButton.onClick.AddListenerAsExclusive(OnGachaAdDrawButtonTapped);
            _gachaSingleDrawButton.onClick.AddListenerAsExclusive(OnGachaSingleDrawButtonTapped);
            _gachaMultiDrawButton.onClick.AddListenerAsExclusive(OnGachaMultiDrawButtonTapped);
            _gachaRatioButton.onClick.AddListenerAsExclusive(OnGachaRatioButtonTapped);
            _gachaDetailButton.onClick.AddListenerAsExclusive(OnGachaDetailButtonTapped);
            
            // 確定枠表示
            _fixedPrizeDescriptionObject.Hidden = model.GachaFixedPrizeDescription.IsEmpty();
            _fixedPrizeDescriptionText.SetText(model.GachaFixedPrizeDescription.Value);
            
            // 残り期間情報の設定
            _limitlessIcon.SetActive(model.GachaRemainingTimeText == GachaRemainingTimeText.Empty);
            _remainingTextParent.SetActive(model.GachaRemainingTimeText != GachaRemainingTimeText.Empty);
            if (model.GachaRemainingTimeText != GachaRemainingTimeText.Empty)
            {
                _remainingTime.SetText(model.GachaRemainingTimeText.Value);
            }
        }

        void SetUpAdGachaButton(PremiumGachaViewModel model)
        {
            // 広告ガチャ設定
            _adGachaButtonInfoGameObject.SetActive(model.AdDrawableFlag);
            _adIntervalText.Hidden = model.AdDrawableFlag;   // 広告ガシャを引ける場合に非表示にする
            _adIntervalText.SetText(model.AdGachaResetRemainingText.Value);
            _adGachaDrawButtonGrayOutImage.gameObject.SetActive(!model.AdDrawableFlag);
            _adGachaDrawButton.interactable = model.AdDrawableFlag.Value;
            _adGachaDrawableCountText.SetText(model.AdGachaDrawableCount.ToRemainingCountString());

            // 通知バッジの表示
            _adNotificationBadgeIcon.gameObject.SetActive(model.NotificationBadge.Value);
            
            // 広告スキップボタンを削除
            _adSkipGachaDrawButton.gameObject.SetActive(false);
        }
        
        void SetUpAdSkipGachaButton(PremiumGachaViewModel model, PassProductName adSkipPassName)
        {
            // 広告ガチャ設定
            _adSkipGachaButtonInfoGameObject.SetActive(model.AdDrawableFlag);
            _adSkipIntervalText.Hidden = model.AdDrawableFlag;   // 広告ガシャを引ける場合に非表示にする
            _adSkipIntervalText.SetText(model.AdGachaResetRemainingText.Value);
            _adSkipDrawableCountText.SetText(model.AdGachaDrawableCount.ToRemainingCountString());
            _adSkipGachaDrawButtonGrayOutImage.gameObject.SetActive(!model.AdDrawableFlag);
            _adSkipGachaDrawButton.interactable = model.AdDrawableFlag.Value;

            // 通知バッジの表示
            _adSkipNotificationBadgeIcon.gameObject.SetActive(model.NotificationBadge.Value);
            
            // パス名を表示
            _adSkipPassNameText.SetText(ZString.Format("{0}適用中", adSkipPassName.ToString()));
            
            // 広告ボタンを削除
            _adGachaDrawButton.gameObject.SetActive(false);
        }

        void OnGachaAdDrawButtonTapped()
        {
            _drawButtonTappedAction?.Invoke(_gachaId, GachaDrawType.Ad);
        }

        void OnGachaSingleDrawButtonTapped()
        {
            _drawButtonTappedAction?.Invoke(_gachaId, GachaDrawType.Single);
        }

        void OnGachaMultiDrawButtonTapped()
        {
            _drawButtonTappedAction?.Invoke(_gachaId, GachaDrawType.Multi);
        }

        void OnGachaRatioButtonTapped()
        {
            _gachaRatioButtonAction?.Invoke(_gachaId);
        }

        void OnGachaDetailButtonTapped()
        {
            _gachaDetailButtonAction?.Invoke(_gachaId);
        }
    }
}
