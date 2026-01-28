using System;
using GLOW.Core.Exceptions;
using GLOW.Scenes.PartyNameEdit.Domain.UseCases;
using GLOW.Scenes.PartyNameEdit.Presentation.ViewModels;
using GLOW.Scenes.PartyNameEdit.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.PartyNameEdit.Presentation.Presenters
{
    public class PartyNameEditDialogPresenter : IPartyNameEditDialogViewDelegate
    {
        [Inject] GetPartyNameUseCase GetPartyNameUseCase { get; }
        [Inject] UpdatePartyNameUseCase UpdatePartyNameUseCase { get; }
        [Inject] PartyNameEditDialogViewController.Argument Args { get; }
        [Inject] PartyNameEditDialogViewController ViewController { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }

        void IPartyNameEditDialogViewDelegate.ViewDidLoad()
        {
            var partyName = GetPartyNameUseCase.GetPartyName(Args.PartyNo);
            var viewModel = new PartyNameEditDialogViewModel(partyName.PartyName);
            ViewController.SetPartyName(viewModel);
        }

        void IPartyNameEditDialogViewDelegate.OnSaveButtonClicked(string newPartyName)
        {
            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async ct =>
            {
                try
                {
                    await UpdatePartyNameUseCase.UpdatePartyName(ct, Args.PartyNo, newPartyName);
                    ViewController.Dismiss();
                }
                catch (PartyInvalidPartyNameException)
                {
                    ViewController.ShowInvalidNameMessage();
                }
                catch (NgWordException)
                {
                    ViewController.ShowInvalidNameMessage();
                }
            });
        }

        void IPartyNameEditDialogViewDelegate.OnCloseButtonClicked()
        {
            ViewController.Dismiss();
        }
    }
}
