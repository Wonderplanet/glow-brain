namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaDiamondRecoverConfirm
{
    public interface IStaminaDiamondRecoverConfirmViewDelegate
    {
        void OnViewDidLoad(StaminaDiamondRecoverConfirmViewController viewController);
        void SpecificCommerceButtonTapped();
        void OnRecoverAtDiamond();
        void TransitionToDiamondShopView();
    }
}
