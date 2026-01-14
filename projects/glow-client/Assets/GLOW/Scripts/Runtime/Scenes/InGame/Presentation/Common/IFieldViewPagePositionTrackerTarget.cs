using GLOW.Scenes.InGame.Presentation.ValueObjects;

namespace GLOW.Scenes.InGame.Presentation.Common
{
    public interface IFieldViewPagePositionTrackerTarget
    {
        FieldViewCoordV2 GetFieldViewCoordPos();
        bool IsDestroyed();
    }
}
