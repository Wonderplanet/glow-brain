using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.GachaList.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.GachaList.Presentation.Views
{
    public class TutorialGachaBannerComponent : UIObject
    {
        [SerializeField] RawImage _bannerRawImage;
        [SerializeField] UIText _descriptionText;
        [SerializeField] Button _gachaMultiDrawButton;
        [SerializeField] Button _lineupButton;
        [SerializeField] GameObject _buttonBackLighitEffect1;
        [SerializeField] GameObject _buttonFrontLighitEffect2;
        

        MasterDataId _gachaId;
        Action<MasterDataId> _drawButtonTappedAction;
        Action<MasterDataId> _lineupButtonTappedAction;

        public void Setup(
            TutorialGachaBannerViewModel model,
            Action<MasterDataId> drawButtonTappedAction,
            Action<MasterDataId> lineupButtonTappedAction)
        {
            _gachaId = model.GachaId;
            
            UIBannerLoaderEx.Main.LoadBannerWithFadeIfNotLoaded(_bannerRawImage, model.GachaBannerAssetPath.Value);
            _descriptionText.SetText(model.GachaDescription.Value);
            
            // ボタンのアクション設定
            _drawButtonTappedAction = drawButtonTappedAction;
            _lineupButtonTappedAction = lineupButtonTappedAction;
            _gachaMultiDrawButton.onClick.AddListenerAsExclusive(OnGachaMultiDrawButtonTapped);
            _lineupButton.onClick.AddListenerAsExclusive(OnLineupButtonTapped);
        }
        
        public void SetButtonEffectActive(bool isActive)
        {
            _buttonBackLighitEffect1.SetActive(isActive);
            _buttonFrontLighitEffect2.SetActive(isActive);
        }
        
        void OnGachaMultiDrawButtonTapped()
        {
            _drawButtonTappedAction?.Invoke(_gachaId);
        }

        void OnLineupButtonTapped()
        {
            _lineupButtonTappedAction?.Invoke(_gachaId);
        }
    }
}
