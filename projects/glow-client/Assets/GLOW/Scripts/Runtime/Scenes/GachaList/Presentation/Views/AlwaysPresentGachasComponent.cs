using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaList.Presentation.ViewModels;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using UnityEngine;

namespace GLOW.Scenes.GachaList.Presentation.Views
{
    public class AlwaysPresentGachasComponent : UIObject
    {
        [SerializeField] PremiumGachaComponent _premiumGachaComponent;
        
        public MasterDataId PremiumGachaId => _premiumGachaComponent.GachaId;
        Action<MasterDataId, GachaDrawType> _drawButtonTappedAction;
        
        public void Setup(
            PremiumGachaViewModel premiumGacha,
            HeldAdSkipPassInfoViewModel heldAdSkipPassInfoViewModel,
            Action<MasterDataId, GachaDrawType> showGachaConfirmDialogAction,
            Action<MasterDataId> gachaRatioAction,
            Action<MasterDataId> gachaDetailAction)
        {
            _drawButtonTappedAction = showGachaConfirmDialogAction;
            SetupPremiumGacha(premiumGacha, heldAdSkipPassInfoViewModel, gachaRatioAction, gachaDetailAction);
        }
        
        void SetupPremiumGacha(
            PremiumGachaViewModel premiumGacha,
            HeldAdSkipPassInfoViewModel heldAdSkipPassInfoViewModel,
            Action<MasterDataId> gachaRatioAction,
            Action<MasterDataId> gachaDetailAction)
        {
            if (premiumGacha.IsEmpty())
            {
                _premiumGachaComponent.gameObject.SetActive(false);
                return;
            }

            _premiumGachaComponent.Setup(
                premiumGacha, 
                heldAdSkipPassInfoViewModel, 
                OnGachaDrawButtonTapped, 
                gachaRatioAction,
                gachaDetailAction);
        }

        void OnGachaDrawButtonTapped(MasterDataId gachaId, GachaDrawType gachaDrawType)
        {
            // controllerのメソッドを呼び出す
            _drawButtonTappedAction?.Invoke(gachaId, gachaDrawType);
        }
    }
}
