using System;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.Mission.Presentation.View.MissionMain
{
    public class MissionScreenInteractionControl : IScreenInteractionControl
    {
        [Inject] MissionMainViewController MissionMainViewController { get; }

        int _lockCount;
        
        public void ActivityBegin()
        {
            if (_lockCount > 0)
            {
                ++_lockCount;
                return;
            }
            
            MissionMainViewController.StartIndicator();
            ++_lockCount;
        }

        public void ActivityEnd()
        {
            --_lockCount;
            if (_lockCount > 0)
            {
                return;
            }
            
            MissionMainViewController.StopIndicator();
        }

        IDisposable IScreenInteractionControl.Lock()
        {
            return this.Activate();
        }
    }
}