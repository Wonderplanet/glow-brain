using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using UnityEngine;

namespace GLOW.Scenes.BoxGachaLineupDialog.Presentation.Component
{
    public class BoxGachaLineupCellComponent : UIObject
    {
        [SerializeField] UIObject _whiteBackgroundImage;
        [SerializeField] UIObject _grayBackgroundImage;
        [SerializeField] PlayerResourceIconButtonComponent _resourceIconButton;
        [SerializeField] UIText _nameText;
        [SerializeField] UIText _stockCountText;
        
        public void SetUpBackground(bool isWhiteBackground)
        {
            _whiteBackgroundImage.IsVisible = isWhiteBackground;
            _grayBackgroundImage.IsVisible = !isWhiteBackground;
        }
        
        public void SetUpPlayerResourceIcon(
            PlayerResourceIconViewModel prizeIconViewModel, 
            Action<PlayerResourceIconViewModel> onPrizeIconSelected)
        {
            _resourceIconButton.Setup(
                prizeIconViewModel,
                () => { 
                    onPrizeIconSelected?.Invoke(prizeIconViewModel); 
                });
        }

        public void SetUpNameText(PlayerResourceName resourceName)
        {
            _nameText.SetText(resourceName.ToString());
        }
        
        public void SetUpStockCountText(BoxGachaPrizeStock prizeStock)
        {
            _stockCountText.SetText(prizeStock.ToString());
        }
    }
}