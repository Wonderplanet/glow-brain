using System;
using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UniRx;

namespace Data
{
	public interface ISampleApi
	{
		IObservable<SampleData[]> Index();
		IObservable<SampleData> Show(int id);
		IObservable<SampleData> Create(int paramName, bool isSample, int? optionalId, Category category);
		IObservable<SampleData> Update(int id, int nestedId, int paramName);
	}

	public class SampleApi : ServerApi, ISampleApi
	{
		public IObservable<SampleData[]> Index()
		{
			return Get<SampleData[]>("/api/path/");
		}

		public IObservable<SampleData> Show(int id)
		{

			return Get<SampleData>(string.Format("/api/path/{0}", id));
		}

		public IObservable<SampleData> Create(int paramName, bool isSample, int? optionalId, Category category)
		{
			var formParams = new WWWForm();
			formParams.AddField("param_name", paramName);
			formParams.AddField("is_sample", isSample ? "true" : "false");
			if(optionalId.HasValue) formParams.AddField("optional_id", optionalId.Value);
			formParams.AddField("category", (int)category);
			return Post<SampleData>("/api/path", formParams);
		}

		public IObservable<SampleData> Update(int id, int nestedId, int paramName)
		{
			var formParams = new WWWForm();
			formParams.AddField("param_name", paramName);

			return Patch<SampleData>(string.Format("/api/path/{0}/nested/{1}", id, nestedId), formParams);
		}

	}
}