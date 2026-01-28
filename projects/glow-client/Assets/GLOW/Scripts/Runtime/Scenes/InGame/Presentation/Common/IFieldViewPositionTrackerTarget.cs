using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Common
{
    public interface IFieldViewPositionTrackerTarget
    {
        Vector2 GetWorldPos();
        bool IsDestroyed();
    }
}
