using GLOW.Modules.MessageView.Presentation;
using Zenject;

namespace GLOW.Core.Presentation.Presenters
{
    public interface ILimitAmountWireframe
    {
        void ShowPaidDiamondPurchaseLimitView();
        void ShowItemPurchaseLimitView();
        void ShowItemReceiveLimitView();
    }

    public class LimitAmountWireframe : ILimitAmountWireframe
    {
        [Inject] IMessageViewUtil MessageViewUtil { get; }

        void ILimitAmountWireframe.ShowPaidDiamondPurchaseLimitView()
        {
            MessageViewUtil.ShowMessageWithClose("確認", "有償プリズムが所持上限を超えているため、\n購入できません。");
        }

        void ILimitAmountWireframe.ShowItemPurchaseLimitView()
        {
            MessageViewUtil.ShowMessageWithClose("確認", "所持上限を超えるため、\n購入できません。");
        }

        void ILimitAmountWireframe.ShowItemReceiveLimitView()
        {
            MessageViewUtil.ShowMessageWithClose("確認", "所持上限を超えるため、\n受け取りできません。");
        }
    }
}
