using System;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverSelect
{
    public class StaminaRecoverSelectViewController :
        UIViewController<StaminaRecoverSelectView>,
        IEscapeResponder
    {
        public record Argument(StaminaRecoverSelectType Type);

        [Inject] IStaminaRecoverSelectViewDelegate ViewDelegate { get; }
        [Inject] Argument Args { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        StaminaRecoverSelectViewModel _viewModel;
        public Action OnDismissAction { get; set; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }
        
        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }
        
        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            EscapeResponderRegistry.Unregister(this);
        }

        public void SetViewModel(StaminaRecoverSelectViewModel viewModel)
        {
            _viewModel = viewModel;
            ActualView.Setup(viewModel);
        }
        
        public void UpdateAdRecoverInterval(bool isUsable, string intervalMinute)
        {
            ActualView.UpdateAdRecoverInterval(isUsable, intervalMinute);
        }
        
        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;
            ViewDelegate.OnClose();
            
            return true;
        }
        
        [UIAction]
        void OnRecoverAtAdButtonTapped()
        {
            ViewDelegate.OnRecoverAtAd(_viewModel.AdvRecoverStaminaValue);
        }

        [UIAction]
        void OnRecoverAtDiamondButtonTapped()
        {
            ViewDelegate.OnRecoverAtDiamond();
            ViewDelegate.OnClose();
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnClose();
        }

    }
}
