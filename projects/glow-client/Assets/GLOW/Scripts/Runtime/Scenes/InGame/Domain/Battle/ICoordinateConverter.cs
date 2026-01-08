using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface ICoordinateConverter
    {
        Matrix3x3 FieldToPlayerOutpostMatrix { get; }
        Matrix3x3 FieldToEnemyOutpostMatrix { get; }
        Matrix3x3 PlayerOutpostToFieldMatrix { get; }
        Matrix3x3 EnemyOutpostToFieldMatrix { get; }
        Matrix3x3 PlayerOutpostToEnemyOutpostMatrix { get; }
        Matrix3x3 EnemyOutpostToPlayerOutpostMatrix { get; }

        void SetTransformationMatrix(Matrix3x3 fieldToPlayerOutpostMatrix, Matrix3x3 fieldToEnemyOutpostMatrix);
        void SetPage(float pageWidth, IReadOnlyList<float> komaHeightList);

        OutpostCoordV2 FieldToOutpostCoord(BattleSide battleSide, FieldCoordV2 fieldCoord);
        OutpostCoordV2 FieldToPlayerOutpostCoord(FieldCoordV2 fieldCoord);
        OutpostCoordV2 FieldToEnemyOutpostCoord(FieldCoordV2 fieldCoord);
        FieldCoordV2 PlayerOutpostToFieldCoord(OutpostCoordV2 outpostCoord);
        FieldCoordV2 EnemyOutpostToFieldCoord(OutpostCoordV2 outpostCoord);
        FieldCoordV2 OutpostToFieldCoord(BattleSide battleSide, OutpostCoordV2 outpostCoord);
        OutpostCoordV2 PlayerOutpostToEnemyOutpostCoord(OutpostCoordV2 playerOutpostCoord);
        OutpostCoordV2 EnemyOutpostToPlayerOutpostCoord(OutpostCoordV2 enemyOutpostCoord);
        OutpostCoordV2 ToFoeOutpostCoord(BattleSide battleSide, OutpostCoordV2 outpostCoord);
        PageCoordV2 FieldToPageCoord(FieldCoordV2 fieldCoord);
        FieldCoordV2 PageToFieldCoord(PageCoordV2 pageCoord);
    }
}
