
using GLOW.Scenes.Login.Domain.UseCase;

namespace GLOW.Scenes.Login.Domain.UseCases
{
    public interface ILoginPhaseNotifier
    {
        void LoginPhaseChanged(LoginPhaseLabel loginPhaseLabel);
        void LoginPhaseDetailChanged(LoginPhaseDetailLabel loginPhaseDetailLabel);
        void LoginPhaseDetailEnded();
    }
}
