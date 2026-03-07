using GLOW.Modules.MessageView.Presentation;

namespace GLOW.Core.Presentation.Modules
{
    public static class ShopPurchaseLimitDialogHelper
    {
        /// <summary>
        /// 購入限度額を超過しているため、購入できないことをユーザに通知するダイアログを表示
        /// </summary>
        public static void ShowDialog(IMessageViewUtil messageViewUtil)
        {
            messageViewUtil.ShowMessageWithClose(
                "確認",
                "購入限度額を超過しているため、\n"
                + "購入いただくことができません。\n\n"
                + "※ないようがわからないときは、\n"
                + "おうちのひとにがめんをみせてください。");
        }
    }
}
