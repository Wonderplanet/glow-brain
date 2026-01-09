using System;
using System.Threading;
using Cysharp.Threading.Tasks;

namespace GLOW.Core.Presentation.Presenters
{
    public interface IInAppPurchaseExecuteDelegate
    {
        void ExecutePurchase(CancellationToken cancellationToken,  Func<CancellationToken, UniTask> purchaseTask);
    }
}
