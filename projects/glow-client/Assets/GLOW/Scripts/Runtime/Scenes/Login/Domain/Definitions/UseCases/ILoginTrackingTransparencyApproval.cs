using Cysharp.Threading.Tasks;

namespace GLOW.Scenes.Login.Domain.UseCases
{
    public interface ILoginTrackingTransparencyApproval
    {
        UniTask ShowTrackingTransparencyConfirmView();
    }
}
