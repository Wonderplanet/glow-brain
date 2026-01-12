using UnityEngine;

namespace GLOW.Core.Extensions
{
    public static class Vector3Extension
    {
        public static Vector2 ToVector2(this Vector3 vector3)
        {
            return new Vector2(vector3.x, vector3.y);
        }
    }
}
