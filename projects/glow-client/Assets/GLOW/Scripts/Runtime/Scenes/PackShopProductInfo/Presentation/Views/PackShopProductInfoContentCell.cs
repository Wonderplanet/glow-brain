using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PackShopProductInfo.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.PackShopProductInfo.Presentation.Views
{
    public class PackShopProductInfoContentCell : UIComponent
    {
        [SerializeField] PlayerResourceIconButtonComponent _button;
        [SerializeField] UIText _name;
        [SerializeField] UIText _amount;
        [SerializeField] UITextButton _ticketDetailButton;

        public void Setup(PackShopProductInfoContentViewModel viewModel, Action<MasterDataId> ticketDetailAction)
        {
            _button.Setup(viewModel.ResourceIcon);

            _name.SetText(viewModel.Name.Value);
            _amount.SetText(viewModel.Amount.ToStringWithMultiplicationAndSeparate());

            _ticketDetailButton.onClick.RemoveAllListeners();
            _ticketDetailButton.gameObject.SetActive(viewModel.IsTicketItem);

            if (viewModel.IsTicketItem)
            {
                _ticketDetailButton.onClick.AddListener(() => ticketDetailAction(viewModel.ResourceIcon.Id));
            }
        }
    }
}
