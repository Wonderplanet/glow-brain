using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.ValueObjects;

namespace GLOW.Scenes.InGame.Presentation.Common
{
    public interface IViewCoordinateConverter
    {
        void SetTransformationMatrix(
            Matrix3x3 fieldToFieldViewMatrix,
            Matrix3x3 fieldToPlayerOutpostMatrix,
            Matrix3x3 fieldToEnemyOutpostMatrix);

        FieldViewCoordV2 ToFieldViewCoord(FieldCoordV2 fieldCoord);
        FieldCoordV2 ToFieldCoord(FieldViewCoordV2 fieldViewCoord);
        FieldViewCoordV2 ToFieldViewCoord(BattleSide battleSide, OutpostCoordV2 outpostCoord);
    }
}
