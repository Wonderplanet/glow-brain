using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.View
{
    public class ArtworkGradeContentsViewController :
        UIViewController<ArtworkGradeContentsView>,
        IEscapeResponder
    {
        public record Argument(MasterDataId MstArtworkId);
        [Inject] IArtworkGradeContentsDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();

            EscapeResponderRegistry.Register(this);
        }

        public void Setup(ArtworkGradeContentsViewModel viewModel, Action<PlayerResourceIconViewModel> onIconTapped)
        {
            ActualView.SetUpView(viewModel, onIconTapped);
        }

        public void CloseView()
        {
            EscapeResponderRegistry.Unregister(this);
            Dismiss();
        }

        public bool OnEscape()
        {
            if(View.Hidden) return false;

            CloseView();
            return true;
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnCloseButtonTapped();
        }
    }
}
