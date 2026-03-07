using UIKit;

namespace WPFramework.Presentation.Modules
{
    public sealed class AlertEscapeResponder : IEscapeResponder
    {
        readonly UIAlertController _controller;
        readonly ISystemSoundEffectProvider _systemSoundEffectProvider;
        readonly bool _enableEscape;
        readonly UIAlertAction _invokeAction;

        public AlertEscapeResponder(UIAlertController controller, bool enableEscape, ISystemSoundEffectProvider systemSoundEffectProvider, UIAlertAction invokeAction = null)
        {
            _systemSoundEffectProvider = systemSoundEffectProvider;
            _controller = controller;
            _enableEscape = enableEscape;
            _invokeAction = invokeAction;
        }

        bool IEscapeResponder.OnEscape()
        {
            if (!_enableEscape)
            {
                return true;
            }

            _systemSoundEffectProvider.PlaySeTap();
            _controller.Dismiss();

            _invokeAction?.Invoke();
            return true;
        }
    }
}
