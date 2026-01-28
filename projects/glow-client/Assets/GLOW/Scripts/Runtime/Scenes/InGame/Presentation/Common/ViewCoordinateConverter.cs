using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Common
{
    public class ViewCoordinateConverter : IViewCoordinateConverter
    {
        Matrix3x3 _fieldToFieldViewMatrix = Matrix3x3.Identity;
        Matrix3x3 _fieldViewToFieldMatrix = Matrix3x3.Identity;
        Matrix3x3 _playerOutpostToFieldViewMatrix = Matrix3x3.Identity;
        Matrix3x3 _enemyOutpostToFieldViewMatrix = Matrix3x3.Identity;

        public void SetTransformationMatrix(
            Matrix3x3 fieldToFieldViewMatrix,
            Matrix3x3 fieldToPlayerOutpostMatrix,
            Matrix3x3 fieldToEnemyOutpostMatrix)
        {
            _fieldToFieldViewMatrix = fieldToFieldViewMatrix;
            _fieldViewToFieldMatrix = fieldToFieldViewMatrix.Inverse();

            _playerOutpostToFieldViewMatrix = fieldToPlayerOutpostMatrix.Inverse() * fieldToFieldViewMatrix;
            _enemyOutpostToFieldViewMatrix = fieldToEnemyOutpostMatrix.Inverse() * fieldToFieldViewMatrix;
        }

        public FieldViewCoordV2 ToFieldViewCoord(FieldCoordV2 fieldCoord)
        {
            Vector2 vec = _fieldToFieldViewMatrix.Multiply(fieldCoord.X, fieldCoord.Y);
            return new FieldViewCoordV2(vec.x, vec.y);
        }

        public FieldCoordV2 ToFieldCoord(FieldViewCoordV2 fieldViewCoord)
        {
            Vector2 vec = _fieldViewToFieldMatrix.Multiply(fieldViewCoord.X, fieldViewCoord.Y);
            return new FieldCoordV2(vec.x, vec.y);
        }

        public FieldViewCoordV2 ToFieldViewCoord(BattleSide battleSide, OutpostCoordV2 outpostCoord)
        {
            var matrix = battleSide switch
            {
                BattleSide.Player => _playerOutpostToFieldViewMatrix,
                BattleSide.Enemy => _enemyOutpostToFieldViewMatrix,
                _ => throw new System.NotImplementedException()
            };

            Vector2 vec = matrix.Multiply(outpostCoord.X, outpostCoord.Y);
            return new FieldViewCoordV2(vec.x, vec.y);
        }
    }
}
