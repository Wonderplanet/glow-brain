using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.TitleBnIdLinkageResultDialog;
using GLOW.Core.Exceptions;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.TitleLinkBnIdDialog.Domain.Models;
using GLOW.Scenes.TitleResultLinkBnIdDialog.Domain.UseCases;
using GLOW.Scenes.TitleResultLinkBnIdDialog.Presentation.ViewModels;
using GLOW.Scenes.TitleResultLinkBnIdDialog.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.TitleResultLinkBnIdDialog.Presentation.Presenters
{
    public class TitleBnIdLinkageBnIdLinkageResultDialogPresenter : ITitleBnIdLinkageResultDialogViewDelegate
    {
        [Inject] TitleBnIdLinkageResultDialogViewController ViewController { get; }
        [Inject] TitleBnIdLinkageResultDialogViewController.Argument Argument { get; }

        void ITitleBnIdLinkageResultDialogViewDelegate.OnViewDidLoad()
        {
            ViewController.Setup(Argument.ViewModel);
        }

        void ITitleBnIdLinkageResultDialogViewDelegate.OnLeftButton()
        {
            ViewController.OnLeftButton?.Invoke();

            if (ViewController.OnLeftButton == null)
            {
                ViewController.Dismiss();
            }
        }

        void ITitleBnIdLinkageResultDialogViewDelegate.OnRightButton()
        {
            ViewController.OnRightButton?.Invoke();

            if (ViewController.OnRightButton == null)
            {
                ViewController.Dismiss();
            }
        }
    }
}
