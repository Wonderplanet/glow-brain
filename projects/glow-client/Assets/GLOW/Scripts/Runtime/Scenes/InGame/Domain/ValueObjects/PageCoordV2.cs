using System;
using UnityEngine;

namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record PageCoordV2(float X, float Y)
    {
        public static PageCoordV2 Empty { get; } = new(0f, 0f);

        public static PageCoordV2 Normalize(PageCoordV2 vec)
        {
            if (vec.X == 0f && vec.Y == 0f)
            {
                return vec;
            }

            var length = Math.Sqrt(vec.X * vec.X + vec.Y * vec.Y);
            return new PageCoordV2(vec.X / (float)length, vec.Y / (float)length);
        }

        public static PageCoordV2 operator -(PageCoordV2 a, PageCoordV2 b)
        {
            return new PageCoordV2(a.X - b.X, a.Y - b.Y);
        }

        public static PageCoordV2 operator +(PageCoordV2 a, PageCoordV2 b)
        {
            return new PageCoordV2(a.X + b.X, a.Y + b.Y);
        }

        public static PageCoordV2 operator *(PageCoordV2 a, float b)
        {
            return new PageCoordV2(a.X * b, a.Y * b);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public float SqrMagnitude()
        {
            return X * X + Y * Y;
        }

        public Vector2 ToVector2()
        {
            return new Vector2(X, Y);
        }
    }
}
