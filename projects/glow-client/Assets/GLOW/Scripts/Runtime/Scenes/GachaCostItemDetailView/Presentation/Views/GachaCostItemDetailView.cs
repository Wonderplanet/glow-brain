using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.GachaCostItemDetailView.Domain.ValueObject;
using GLOW.Scenes.ItemDetail.Presentation.Views;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.GachaCostItemDetailView.Presentation.Views
{
    /// <summary>
    /// 81_アイテムBOXリスト
    /// 　81-3_アイテムBOXページダイアログ
    /// 　　81-3-6_ガシャチケット詳細画面
    /// </summary>
    public class GachaCostItemDetailView : UIView
    {
        [SerializeField] PlayerResourceIconComponent _playerResourceIconComponent;
        [SerializeField] UIText _amountText;
        [SerializeField] UIText _itemNameText;
        [SerializeField] UIText _itemDescriptionText;
        [SerializeField] ScrollRect _descriptionScrollRect;
        [SerializeField] LayoutElement _descriptionLayoutElement;
        [SerializeField] UIObject _transitionArea;
        [SerializeField] UIObject _transitionButtonGrayOutObject;

        public void SetUpPlayerResourceIconComponent(PlayerResourceIconViewModel model)
        {
            _playerResourceIconComponent.Setup(model);
        }

        public void SetAmountText(PlayerResourceAmount amount)
        {
            _amountText.SetText(AmountFormatter.FormatAmount(amount.Value));
        }
        
        public void SetTransitionAreaVisible(ShowTransitAreaFlag isTransitAreaVisible)
        {
            _transitionArea.Hidden = !isTransitAreaVisible;
        }
        
        public void SetTransitionButtonGrayout(TransitionButtonGrayOutFlag isTransitionButtonGrayOut)
        {
            _transitionButtonGrayOutObject.Hidden = !isTransitionButtonGrayOut;
        }
        
        public void SetNameText(PlayerResourceName name)
        {
            _itemNameText.SetText(name.Value);
        }
        
        public void SetDescriptionText(PlayerResourceDescription description)
        {
            _itemDescriptionText.SetText(description.Value);
            DisableDescriptionScrollIfNotNeeded();
        }
        
        void DisableDescriptionScrollIfNotNeeded()
        {
            LayoutRebuilder.ForceRebuildLayoutImmediate(_descriptionScrollRect.content);

            if (_descriptionScrollRect.content.sizeDelta.y <= _descriptionLayoutElement.minHeight)
            {
                _descriptionScrollRect.enabled = false;
                _descriptionScrollRect.verticalScrollbar.gameObject.SetActive(false);
            }
        }
    }
}