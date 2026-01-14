using System;
using GLOW.Core.Presentation.Modules.Audio;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Modules.TutorialTipDialog.Presentation.View
{
    public class TutorialTipDialogViewEscapeResponder : IEscapeResponder
    {
        readonly TutorialTipDialogViewController _controller;
        readonly SoundEffectId _soundEffectId;

        public TutorialTipDialogViewEscapeResponder(
            TutorialTipDialogViewController controller,
            SoundEffectId soundEffectId = SoundEffectId.None)
        {
            _controller = controller;
            _soundEffectId = soundEffectId;
        }

        bool IEscapeResponder.OnEscape()
        {
            SoundEffectPlayer.Play(_soundEffectId);
            _controller.CloseButtonTapped();

            return true;
        }
    }
}
