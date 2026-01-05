using GLOW.Core.Modules.MultipleSwitchController;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.TimelineTracks
{
    public interface IScreenFlashTrackClipDelegate
    {
        MultipleSwitchHandler StartFlash();
        Object GetObjectForBinding();
    }
}
