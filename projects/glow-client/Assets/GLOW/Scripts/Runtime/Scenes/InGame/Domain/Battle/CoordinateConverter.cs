using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class CoordinateConverter : ICoordinateConverter
    {
        public Matrix3x3 FieldToPlayerOutpostMatrix { get; private set; } = Matrix3x3.Identity;
        public Matrix3x3 FieldToEnemyOutpostMatrix { get; private set; } = Matrix3x3.Identity;
        public Matrix3x3 PlayerOutpostToFieldMatrix { get; private set; } = Matrix3x3.Identity;
        public Matrix3x3 EnemyOutpostToFieldMatrix { get; private set; } = Matrix3x3.Identity;
        public Matrix3x3 PlayerOutpostToEnemyOutpostMatrix { get; private set; } = Matrix3x3.Identity;
        public Matrix3x3 EnemyOutpostToPlayerOutpostMatrix { get; private set; } = Matrix3x3.Identity;

        float _pageWidth;
        IReadOnlyList<float> _komaHeightList;    // 最上段のコマから順に段ごとのコマの高さ

        public void SetTransformationMatrix(Matrix3x3 fieldToPlayerOutpostMatrix, Matrix3x3 fieldToEnemyOutpostMatrix)
        {
            FieldToPlayerOutpostMatrix = fieldToPlayerOutpostMatrix;
            FieldToEnemyOutpostMatrix = fieldToEnemyOutpostMatrix;

            PlayerOutpostToFieldMatrix = fieldToPlayerOutpostMatrix.Inverse();
            EnemyOutpostToFieldMatrix = fieldToEnemyOutpostMatrix.Inverse();

            PlayerOutpostToEnemyOutpostMatrix = PlayerOutpostToFieldMatrix * FieldToEnemyOutpostMatrix;
            EnemyOutpostToPlayerOutpostMatrix = EnemyOutpostToFieldMatrix * FieldToPlayerOutpostMatrix;
        }

        public void SetPage(float pageWidth, IReadOnlyList<float> komaHeightList)
        {
            _pageWidth = pageWidth;
            _komaHeightList = komaHeightList;
        }

        public OutpostCoordV2 FieldToOutpostCoord(BattleSide battleSide, FieldCoordV2 fieldCoord)
        {
            return battleSide switch
            {
                BattleSide.Player => FieldToPlayerOutpostCoord(fieldCoord),
                BattleSide.Enemy => FieldToEnemyOutpostCoord(fieldCoord),
                _ => OutpostCoordV2.Empty
            };
        }

        public OutpostCoordV2 FieldToPlayerOutpostCoord(FieldCoordV2 fieldCoord)
        {
            Vector2 vec = FieldToPlayerOutpostMatrix.Multiply(fieldCoord.X, fieldCoord.Y);
            return new OutpostCoordV2(vec.x, vec.y);
        }

        public OutpostCoordV2 FieldToEnemyOutpostCoord(FieldCoordV2 fieldCoord)
        {
            Vector2 vec = FieldToEnemyOutpostMatrix.Multiply(fieldCoord.X, fieldCoord.Y);
            return new OutpostCoordV2(vec.x, vec.y);
        }

        public FieldCoordV2 PlayerOutpostToFieldCoord(OutpostCoordV2 outpostCoord)
        {
            Vector2 vec = PlayerOutpostToFieldMatrix.Multiply(outpostCoord.X, outpostCoord.Y);
            return new FieldCoordV2(vec.x, vec.y);
        }

        public FieldCoordV2 EnemyOutpostToFieldCoord(OutpostCoordV2 outpostCoord)
        {
            Vector2 vec = EnemyOutpostToFieldMatrix.Multiply(outpostCoord.X, outpostCoord.Y);
            return new FieldCoordV2(vec.x, vec.y);
        }

        public FieldCoordV2 OutpostToFieldCoord(BattleSide battleSide, OutpostCoordV2 outpostCoord)
        {
            return battleSide switch
            {
                BattleSide.Player => PlayerOutpostToFieldCoord(outpostCoord),
                BattleSide.Enemy => EnemyOutpostToFieldCoord(outpostCoord),
                _ => FieldCoordV2.Empty
            };
        }

        public OutpostCoordV2 PlayerOutpostToEnemyOutpostCoord(OutpostCoordV2 playerOutpostCoord)
        {
            Vector2 vec = PlayerOutpostToEnemyOutpostMatrix.Multiply(playerOutpostCoord.X, playerOutpostCoord.Y);
            return new OutpostCoordV2(vec.x, vec.y);
        }

        public OutpostCoordV2 EnemyOutpostToPlayerOutpostCoord(OutpostCoordV2 enemyOutpostCoord)
        {
            Vector2 vec = EnemyOutpostToPlayerOutpostMatrix.Multiply(enemyOutpostCoord.X, enemyOutpostCoord.Y);
            return new OutpostCoordV2(vec.x, vec.y);
        }

        public OutpostCoordV2 ToFoeOutpostCoord(BattleSide battleSide, OutpostCoordV2 outpostCoord)
        {
            return battleSide switch
            {
                BattleSide.Player => PlayerOutpostToEnemyOutpostCoord(outpostCoord),
                BattleSide.Enemy => EnemyOutpostToPlayerOutpostCoord(outpostCoord),
                _ => OutpostCoordV2.Empty
            };
        }

        public PageCoordV2 FieldToPageCoord(FieldCoordV2 fieldCoord)
        {
            int komaLine = Mathf.Clamp(Mathf.FloorToInt(fieldCoord.X / _pageWidth), 0, _komaHeightList.Count-1);

            float x = fieldCoord.X - komaLine * _pageWidth;

            float y = 0f;
            for (int i = 0; i < komaLine; i++)
            {
                y += _komaHeightList[i];
            }
            y += _komaHeightList[komaLine] - fieldCoord.Y;

            return new PageCoordV2(x, y);
        }

        public FieldCoordV2 PageToFieldCoord(PageCoordV2 pageCoord)
        {
            float y = pageCoord.Y;
            int komaLine = 0;

            for (int i = 0; i < _komaHeightList.Count; i++)
            {
                y -= _komaHeightList[i];
                if (y <= 0f) break;

                komaLine++;
            }
            y = -y;

            float x = komaLine * _pageWidth + pageCoord.X;

            return new FieldCoordV2(x, y);
        }
    }
}
