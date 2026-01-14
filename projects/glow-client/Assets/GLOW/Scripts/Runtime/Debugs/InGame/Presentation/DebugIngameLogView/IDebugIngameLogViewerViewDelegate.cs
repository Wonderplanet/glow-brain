#if GLOW_INGAME_DEBUG
using System;
using GLOW.Debugs.InGame.Domain.Models;
using GLOW.Debugs.InGame.Domain.UseCases;

namespace GLOW.Debugs.InGame.Presentation.DebugIngameLogView
{
    public interface IDebugIngameLogViewerViewDelegate
    {
        void Init(Action<DebugInGameLogDamageModel> onDamageReport);
        void ViewDidDisappear();

    }
}
#endif