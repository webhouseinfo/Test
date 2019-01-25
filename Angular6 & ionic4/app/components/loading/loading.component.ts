import { Component, OnInit, Input } from '@angular/core';

@Component({
  selector: 'app-loading',
  templateUrl: './loading.component.html',
  styleUrls: ['./loading.component.scss']
})
export class loading implements OnInit {
  @Input() show: boolean = true;

  constructor() { }

  ngOnInit() {
  }

}
