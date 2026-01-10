using System;
using GLOW.Core.Presentation.Modules.Audio;
using WPFramework.Presentation.Modules;

namespace GLOW.Modules.MessageView.Presentation
{
    public sealed class MessageViewEscapeResponder : IEscapeResponder
    {
        readonly MessageViewController _controller;
        readonly ISystemSoundEffectProvider _systemSoundEffectProvider;
        readonly bool _enableEscape;
        readonly SoundEffectId _soundEffectId;
        readonly Action _invokeAction;

        public MessageViewEscapeResponder(
            MessageViewController controller,
            bool enableEscape,
            ISystemSoundEffectProvider systemSoundEffectProvider,
            SoundEffectId soundEffectId,
            Action invokeAction = null)
        {
            _systemSoundEffectProvider = systemSoundEffectProvider;
            _controller = controller;
            _enableEscape = enableEscape;
            _soundEffectId = soundEffectId;
            _invokeAction = invokeAction;
        }

        bool IEscapeResponder.OnEscape()
        {
            if (!_enableEscape)
            {
                return true;
            }

            SoundEffectPlayer.Play(_soundEffectId);
            _controller.Dismiss();

            _invokeAction?.Invoke();
            return true;
        }
    }
}
