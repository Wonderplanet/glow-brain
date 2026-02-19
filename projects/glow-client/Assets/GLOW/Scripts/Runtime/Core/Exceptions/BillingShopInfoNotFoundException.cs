using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    /// <summary>
    /// サーバーエラー：ショップ情報レコードが存在していない
    /// </summary>
    public class BillingShopInfoNotFoundException : ServerBillingException
    {
        public BillingShopInfoNotFoundException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
