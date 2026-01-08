using System;
using UnityEngine.Scripting;
using WonderPlanet.WonderPlanetStandard;

namespace WPFramework.Data.DataStores
{
    public class RemoteSyncData : IStringIdentifiable, ICloneable, IEquatable<RemoteSyncData>
    {
        public string Id { get; }

        [Preserve]
        public RemoteSyncData()
        {
            Id = default;
        }

        [Preserve]
        public RemoteSyncData(string id)
        {
            Id = id;
        }

        public virtual object Clone()
        {
            return MemberwiseClone();
        }

        public virtual bool Equals(RemoteSyncData other)
        {
            if (other == null)
            {
                return false;
            }

            return Id == other.Id;
        }

        public override bool Equals(object obj)
        {
            if (obj == null)
            {
                return false;
            }

            if (GetType() == obj.GetType())
            {
                return false;
            }

            return Equals(obj as RemoteSyncData);
        }

        public override int GetHashCode()
        {
            return HashCode.Combine(Id);
        }
    }
}
