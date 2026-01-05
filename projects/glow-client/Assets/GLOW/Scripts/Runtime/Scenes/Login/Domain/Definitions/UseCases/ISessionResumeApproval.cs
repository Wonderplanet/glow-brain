using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Login.Domain.UseCases
{
    public interface ISessionResumeApproval
    {
        UniTask<bool> ShowResumeSessionConfirmView(
            CancellationToken cancellationToken,
            InGameContentType inGameContentType,
            MasterDataId targetMstId);
    }
}
